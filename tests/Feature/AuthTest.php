<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Division;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up roles
        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        // Set up dummy division
        $this->division = Division::create([
            'name' => 'Teknologi Informasi',
            'code' => 'IT',
            'is_active' => true
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $response = $this->post('/login', [
            'username' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_employee_is_redirected_to_user_dashboard(): void
    {
        $employee = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@example.com',
            'employee_number' => 'PEG-001',
            'password' => Hash::make('password'),
            'division_id' => $this->division->id,
            'is_active' => true,
        ]);
        $employee->assignRole('user');

        // Test login with email
        $response1 = $this->post('/login', [
            'username' => 'ahmad.fauzi@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response1->assertRedirect('/dashboard');

        $this->post('/logout');
        $this->assertGuest();

        // Test login with employee number (NIP)
        $response2 = $this->post('/login', [
            'username' => 'PEG-001',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response2->assertRedirect('/dashboard');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);
        $inactive->assignRole('user');

        $response = $this->post('/login', [
            'username' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }
}
