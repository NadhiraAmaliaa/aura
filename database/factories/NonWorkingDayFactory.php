<?php

namespace Database\Factories;

use App\Models\NonWorkingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NonWorkingDay>
 */
class NonWorkingDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d'),
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement(array_keys(NonWorkingDay::typeLabels())),
        ];
    }
}
