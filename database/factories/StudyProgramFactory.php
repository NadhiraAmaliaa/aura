<?php

namespace Database\Factories;

use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyProgram>
 */
class StudyProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_id' => University::factory(),
            'name' => fake()->randomElement([
                'Teknik Informatika',
                'Sistem Informasi',
                'Manajemen',
                'Akuntansi',
                'Teknik Elektro',
                'Ilmu Komunikasi',
            ]),
            'level' => fake()->randomElement(['D-III', 'D-IV', 'S1']),
        ];
    }
}
