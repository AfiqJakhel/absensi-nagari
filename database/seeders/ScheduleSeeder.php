<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today()->toDateString();

        // Create schedule for today
        $schedule = Schedule::updateOrCreate(
            ['attendance_date' => $today],
            [
                'name' => 'Jadwal Harian (Kantor Wali Nagari)',
                'start_time' => '08:00:00',
                'end_time' => '16:30:00',
                'check_in_start' => '07:00:00',
                'check_in_end' => '12:00:00',
                'check_out_start' => '16:00:00',
                'check_out_end' => '22:00:00',
                'late_tolerance_minutes' => 15,
                'location_name' => 'Kantor Wali Nagari',
                'latitude' => -1.583488,
                'longitude' => 100.865324,
                'radius_meters' => 150,
                'location_validation_enabled' => false, // Set to false by default for easy local testing
                'is_active' => true,
                'notes' => 'Jadwal reguler pegawai kantor Wali Nagari.',
            ]
        );

        // Attach all users with the role 'user' (pegawai)
        $employees = User::role('user')->get();

        foreach ($employees as $employee) {
            $schedule->users()->syncWithoutDetaching([$employee->id]);
        }
    }
}
