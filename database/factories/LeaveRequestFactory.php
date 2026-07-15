<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    /**
     * Define the model's default state.
     *
     * The request number is intentionally left unset so the model's `creating`
     * hook generates a unique, human-readable value (LR-YYYYMMDD-NNNN).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::tomorrow();

        return [
            'user_id' => User::factory()->state(['role' => 'intern']),
            'type' => fake()->randomElement(['izin', 'sakit']),
            'reason' => fake()->sentence(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->toDateString(),
            'total_days' => 1,
            'contact_phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'evidence_path' => null,
            'status' => 'pending',
            'admin_note' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * A request of type "izin".
     */
    public function izin(): static
    {
        return $this->state(fn (): array => ['type' => 'izin']);
    }

    /**
     * A request of type "sakit" (carries evidence by convention).
     */
    public function sakit(): static
    {
        return $this->state(fn (): array => [
            'type' => 'sakit',
            'evidence_path' => 'leave-evidence/'.fake()->uuid().'.pdf',
        ]);
    }

    /**
     * An approved request, decided by the given (or a new) approver.
     */
    public function approved(?User $approver = null): static
    {
        return $this->state(fn (): array => [
            'status' => 'approved',
            'approved_by' => $approver?->id ?? User::factory()->state(['role' => 'admin']),
            'approved_at' => now(),
        ]);
    }

    /**
     * A rejected request, decided by the given (or a new) approver.
     */
    public function rejected(?User $approver = null): static
    {
        return $this->state(fn (): array => [
            'status' => 'rejected',
            'admin_note' => fake()->sentence(),
            'approved_by' => $approver?->id ?? User::factory()->state(['role' => 'admin']),
            'approved_at' => now(),
        ]);
    }
}
