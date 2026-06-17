<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Seed an initial set of placement divisions.
     *
     * These are starting values; administrators manage the list afterwards via
     * the master-data screen.
     */
    public function run(): void
    {
        $divisions = [
            'Teknologi Informasi',
            'Sumber Daya Manusia',
            'Keuangan',
            'Pemasaran',
            'Operasional',
            'Sekretariat',
            'Hukum',
            'Pengadaan',
        ];

        foreach ($divisions as $name) {
            Division::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
