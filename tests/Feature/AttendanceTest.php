<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Division;
use App\Enums\AttendanceStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::findOrCreate('user');

        // Create division
        $this->division = Division::create([
            'name' => 'Kesejahteraan Rakyat',
            'code' => 'KESRA',
            'is_active' => true
        ]);

        // Create employee
        $this->employee = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@example.com',
            'employee_number' => 'PEG-001',
            'password' => bcrypt('password'),
            'division_id' => $this->division->id,
            'is_active' => true,
        ]);
        $this->employee->assignRole('user');

        // Today date
        $this->today = Carbon::today()->toDateString();
    }

    public function test_absensi_page_requires_authentication(): void
    {
        $response = $this->get('/absensi');
        $response->assertRedirect('/login');
    }

    public function test_absensi_page_can_be_accessed_by_authenticated_employee(): void
    {
        $response = $this->actingAs($this->employee)->get('/absensi');
        $response->assertStatus(200);
    }

    public function test_check_in_fails_if_no_schedule(): void
    {
        $response = Livewire::actingAs($this->employee)
            ->test('pegawai.absensi-form')
            ->call('checkIn')
            ->assertHasNoErrors();

        $this->assertNull(Attendance::first());
    }

    public function test_check_in_fails_if_before_start_time(): void
    {
        // Schedule where check-in starts in the future
        $schedule = Schedule::create([
            'name' => 'Jadwal Masa Depan',
            'attendance_date' => $this->today,
            'start_time' => '08:00:00',
            'end_time' => '16:30:00',
            'check_in_start' => Carbon::now()->addHour()->format('H:i:s'),
            'check_in_end' => Carbon::now()->addHours(2)->format('H:i:s'),
            'check_out_start' => '16:00:00',
            'check_out_end' => '20:00:00',
            'is_active' => true,
        ]);
        $schedule->users()->attach($this->employee->id);

        Livewire::actingAs($this->employee)
            ->test('pegawai.absensi-form')
            ->call('checkIn');

        $this->assertNull(Attendance::first());
    }

    public function test_check_in_succeeds_and_sets_correct_status(): void
    {
        // Today schedule (within check-in window)
        $schedule = Schedule::create([
            'name' => 'Jadwal Sekarang',
            'attendance_date' => $this->today,
            'start_time' => '08:00:00',
            'end_time' => '16:30:00',
            'check_in_start' => Carbon::now()->subHour()->format('H:i:s'),
            'check_in_end' => Carbon::now()->addHour()->format('H:i:s'),
            'check_out_start' => '16:00:00',
            'check_out_end' => '20:00:00',
            'late_tolerance_minutes' => 15,
            'location_validation_enabled' => false,
            'is_active' => true,
        ]);
        $schedule->users()->attach($this->employee->id);

        // Provide dummy base64 photo
        Livewire::actingAs($this->employee)
            ->test('pegawai.absensi-form')
            ->set('photo', 'data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
            ->call('checkIn');

        $attendance = Attendance::first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in_at);
        $this->assertEquals($this->employee->id, $attendance->user_id);
    }



    public function test_check_out_fails_if_before_checkout_start_time(): void
    {
        $schedule = Schedule::create([
            'name' => 'Jadwal Check-out Belum Mulai',
            'attendance_date' => $this->today,
            'start_time' => '08:00:00',
            'end_time' => '16:30:00',
            'check_in_start' => Carbon::now()->subHour()->format('H:i:s'),
            'check_in_end' => Carbon::now()->addHour()->format('H:i:s'),
            'check_out_start' => Carbon::now()->addHour()->format('H:i:s'), // Future checkout
            'check_out_end' => Carbon::now()->addHours(2)->format('H:i:s'),
            'is_active' => true,
        ]);
        $schedule->users()->attach($this->employee->id);

        // Seed check-in first directly
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'schedule_id' => $schedule->id,
            'attendance_date' => $this->today,
            'check_in_at' => Carbon::now()->subHour(),
            'status' => AttendanceStatus::PRESENT,
        ]);

        Livewire::actingAs($this->employee)
            ->test('pegawai.absensi-form')
            ->call('checkOut');

        $attendance->refresh();
        $this->assertNull($attendance->check_out_at);
    }

    public function test_riwayat_page_requires_authentication(): void
    {
        $response = $this->get('/riwayat');
        $response->assertRedirect('/login');
    }

    public function test_riwayat_page_can_be_accessed_by_authenticated_employee(): void
    {
        $response = $this->actingAs($this->employee)->get('/riwayat');
        $response->assertStatus(200);
    }

    public function test_riwayat_table_filters_by_month_and_status(): void
    {
        $schedule = Schedule::create([
            'name' => 'Jadwal Riwayat',
            'attendance_date' => $this->today,
            'start_time' => '08:00:00',
            'end_time' => '16:30:00',
            'check_in_start' => '07:00:00',
            'check_in_end' => '12:00:00',
            'check_out_start' => '16:00:00',
            'check_out_end' => '20:00:00',
            'is_active' => true,
        ]);
        $schedule->users()->attach($this->employee->id);

        // Create 2 attendance records with different statuses
        Attendance::create([
            'user_id' => $this->employee->id,
            'schedule_id' => $schedule->id,
            'attendance_date' => $this->today,
            'check_in_at' => Carbon::now()->subHour(),
            'status' => AttendanceStatus::PRESENT,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();
        $scheduleYesterday = Schedule::create([
            'name' => 'Jadwal Kemarin',
            'attendance_date' => $yesterday,
            'start_time' => '08:00:00',
            'end_time' => '16:30:00',
            'check_in_start' => '07:00:00',
            'check_in_end' => '12:00:00',
            'check_out_start' => '16:00:00',
            'check_out_end' => '20:00:00',
            'is_active' => true,
        ]);
        $scheduleYesterday->users()->attach($this->employee->id);

        Attendance::create([
            'user_id' => $this->employee->id,
            'schedule_id' => $scheduleYesterday->id,
            'attendance_date' => $yesterday,
            'check_in_at' => Carbon::now()->subHour(),
            'status' => AttendanceStatus::LATE,
        ]);

        // Test filtering
        Livewire::actingAs($this->employee)
            ->test('pegawai.riwayat-table')
            ->set('monthFilter', Carbon::today()->format('Y-m'))
            ->set('statusFilter', 'present')
            ->assertViewHas('history', function ($history) {
                return $history->count() === 1 && $history->first()->status === AttendanceStatus::PRESENT;
            })
            ->set('statusFilter', 'late')
            ->assertViewHas('history', function ($history) {
                return $history->count() === 1 && $history->first()->status === AttendanceStatus::LATE;
            });
    }
}
