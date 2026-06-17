<?php

namespace Database\Seeders;

use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHourSeeder extends Seeder
{
    /**
     * Seed the default working-hour configuration.
     */
    public function run(): void
    {
        WorkingHour::ensureSeeded();
    }
}
