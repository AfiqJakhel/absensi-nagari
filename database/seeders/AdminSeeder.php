<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'), // password default
                'is_active' => true,
            ]
        );
        
        // ensure admin role exists before assigning
        if (\Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            $admin->assignRole('admin');
        }
    }
}
