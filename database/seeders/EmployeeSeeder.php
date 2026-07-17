<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = Division::all();

        if ($divisions->isEmpty()) {
            $this->command->warn('No divisions found. Please seed divisions first.');
            return;
        }

        // List of realistic Indonesian names for Nagari Kambang employees
        $employees = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@example.com', 'phone' => '081234567890', 'nip' => 'PEG-001'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@example.com', 'phone' => '082234567891', 'nip' => 'PEG-002'],
            ['name' => 'Citra Lestari', 'email' => 'citra.lestari@example.com', 'phone' => '083234567892', 'nip' => 'PEG-003'],
            ['name' => 'Dewi Sartika', 'email' => 'dewi.sartika@example.com', 'phone' => '085234567893', 'nip' => 'PEG-004'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@example.com', 'phone' => '087234567894', 'nip' => 'PEG-005'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@example.com', 'phone' => '089234567895', 'nip' => 'PEG-006'],
            ['name' => 'Guntur Prabowo', 'email' => 'guntur.prabowo@example.com', 'phone' => '081334567896', 'nip' => 'PEG-007'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@example.com', 'phone' => '082334567897', 'nip' => 'PEG-008'],
            ['name' => 'Indah Permata', 'email' => 'indah.permata@example.com', 'phone' => '083334567898', 'nip' => 'PEG-009'],
            ['name' => 'Joko Susilo', 'email' => 'joko.susilo@example.com', 'phone' => '085334567899', 'nip' => 'PEG-010']
        ];

        foreach ($employees as $emp) {
            $user = User::firstOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['name'],
                    'password' => Hash::make('password'), // password default: password
                    'employee_number' => $emp['nip'],
                    'phone' => $emp['phone'],
                    'division_id' => $divisions->random()->id,
                    'is_active' => true,
                ]
            );

            // Assign the 'user' role
            if (Role::where('name', 'user')->exists()) {
                $user->assignRole('user');
            }
        }
    }
}
