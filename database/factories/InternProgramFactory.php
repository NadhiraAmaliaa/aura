<?php

namespace Database\Factories;

use App\Models\InternProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternProgram>
 */
class InternProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Magenta',
                'Kerja Praktek',
                'PKL',
                'Kampus Merdeka',
                'Internship Mandiri'
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
