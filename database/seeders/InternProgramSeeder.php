<?php

namespace Database\Seeders;

use App\Models\InternProgram;
use Illuminate\Database\Seeder;

class InternProgramSeeder extends Seeder
{
    /**
     * Seed the application's intern programs.
     */
    public function run(): void
    {
        $programs = [
            ['name' => 'Magenta', 'description' => 'Magang Generasi Bertalenta program.'],
            ['name' => 'Kerja Praktek', 'description' => 'Mandatory work practice for university students.'],
            ['name' => 'PKL', 'description' => 'Praktik Kerja Lapangan for vocational students.'],
            ['name' => 'MBKM', 'description' => 'Merdeka Belajar Kampus Merdeka program.'],
            ['name' => 'Internship Mandiri', 'description' => 'Self-initiated independent internship.'],
        ];

        foreach ($programs as $program) {
            InternProgram::firstOrCreate(['name' => $program['name']], $program);
        }
    }
}
