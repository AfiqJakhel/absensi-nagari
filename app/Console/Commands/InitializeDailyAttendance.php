<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;

class InitializeDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:initialize-today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize today\'s attendance status for all scheduled employees to Alfa (Absent) or Leave/Sick';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $this->info("Initializing daily attendance records for: {$today}");

        // 1. Get all active schedules for today
        $schedules = Schedule::whereDate('attendance_date', $today)
            ->where('is_active', true)
            ->with('users')
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('No active schedules found for today.');
            return 0;
        }

        $initializedCount = 0;
        $skippedCount = 0;

        foreach ($schedules as $schedule) {
            foreach ($schedule->users as $user) {
                // Check if an attendance record already exists for this user, schedule, and date
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('attendance_date', $today)
                    ->first();

                if ($existingAttendance) {
                    $skippedCount++;
                    continue;
                }

                // Check if there is an approved leave or sick request for this user for today
                $approvedLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->first();

                $status = AttendanceStatus::ABSENT; // Default status is Alfa (Absent)
                $notes = 'Alfa (Belum melakukan check-in)';

                if ($approvedLeave) {
                    if ($approvedLeave->type === 'sick') {
                        $status = AttendanceStatus::SICK;
                        $notes = 'Sakit (Disetujui otomatis dari pengajuan sakit)';
                    } else {
                        $status = AttendanceStatus::PERMISSION;
                        $notes = 'Izin (Disetujui otomatis dari pengajuan izin)';
                    }
                }

                // Initialize the attendance record
                Attendance::create([
                    'user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'attendance_date' => $today,
                    'status' => $status,
                    'notes' => $notes,
                    'late_minutes' => 0,
                ]);

                $initializedCount++;
            }
        }

        $this->info("Inisialisasi selesai! Berhasil membuat {$initializedCount} data absensi hari ini (Alfa/Izin). Dilewati: {$skippedCount} data (sudah terdaftar).");
        return 0;
    }
}
