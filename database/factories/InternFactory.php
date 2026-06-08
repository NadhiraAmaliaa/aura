<?php

namespace Database\Factories;

use App\Models\Intern;
use App\Models\InternProgram;
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
            'user_id' => User::factory(),
            'nim' => fake()->unique()->numerify('##########'),
            'phone' => fake()->phoneNumber(),
            'university' => fake()->company().' University',
            'major' => fake()->randomElement([
                'Informatika',
                'Sistem Informasi',
                'Teknik Elektro',
                'Manajemen',
                'Akuntansi',
            ]),
            'division' => fake()->randomElement([
                'IT',
                'Human Resources',
                'Finance',
                'Marketing',
                'Operations',
            ]),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => fake()->randomElement(['active', 'inactive', 'completed']),
        ];
    }
}
