<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['name' => 'Teknologi Informasi', 'code' => 'IT'],
            ['name' => 'Keuangan', 'code' => 'FIN'],
            ['name' => 'Sumber Daya Manusia', 'code' => 'HR'],
            ['name' => 'Operasional', 'code' => 'OPS'],
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate(
                ['code' => $division['code']],
                ['name' => $division['name'], 'is_active' => true]
            );
        }
    }
}
