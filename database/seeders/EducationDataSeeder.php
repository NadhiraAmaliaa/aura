<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class EducationDataSeeder extends Seeder
{
    /**
     * Seed universities and study programs from the bundled CSV datasets.
     *
     * Delegates to the data:import-education command so the import logic lives
     * in one place. Skipped when the data has already been imported.
     */
    public function run(): void
    {
        if (University::query()->exists()) {
            $this->command?->info('Universities already present; skipping education data import.');

            return;
        }

        Artisan::call('data:import-education', [], $this->command?->getOutput());
    }
}
