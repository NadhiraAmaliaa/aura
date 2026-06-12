<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('today 07:00', 'today 09:00');
        $checkOut = fake()->dateTimeBetween('today 15:00', 'today 18:00');

        return [
            'user_id' => User::factory(),
            'attendance_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'check_in_time' => $checkIn->format('H:i'),
            'check_out_time' => $checkOut->format('H:i'),
            'check_in_latitude' => fake()->optional()->latitude(-90, 90),
            'check_in_longitude' => fake()->optional()->longitude(-180, 180),
            'check_out_latitude' => fake()->optional()->latitude(-90, 90),
            'check_out_longitude' => fake()->optional()->longitude(-180, 180),
            'status' => fake()->randomElement(['present', 'late', 'sick', 'permission', 'absent']),
            'work_mode' => fake()->randomElement([
                Attendance::WORK_MODE_WFO,
                Attendance::WORK_MODE_WFH,
                Attendance::WORK_MODE_DINAS,
            ]),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
