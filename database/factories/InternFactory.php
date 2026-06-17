<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Intern;
use App\Models\InternProgram;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Intern>
 */
class InternFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', 'now');
        $endDate = fake()->dateTimeBetween($startDate, '+3 months');

        return [
            'intern_program_id' => InternProgram::factory(),
            'university_id' => University::factory(),
            'study_program_id' => function (array $attributes): StudyProgram {
                // Keep the study program within the intern's university.
                return StudyProgram::factory()->create([
                    'university_id' => $attributes['university_id'],
                ]);
            },
            'division_id' => Division::factory(),
            'user_id' => User::factory()->state(['role' => 'intern', 'nik' => null]),
            'nim' => fake()->unique()->numerify('##########'),
            'phone' => fake()->phoneNumber(),
            'university' => null,
            'major' => null,
            'division' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => Intern::STATUS_ACTIVE,
        ];
    }
}
