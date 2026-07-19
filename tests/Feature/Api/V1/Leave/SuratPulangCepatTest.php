<?php

namespace Tests\Feature\Api\V1\Leave;

use App\Models\Intern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuratPulangCepatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-06 09:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function activeInternUser(): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => Intern::STATUS_ACTIVE,
        ]);

        return $user->fresh();
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'early_leave_date' => '2026-07-06',
            'leave_time' => '14:30',
            'reason' => 'Ada keperluan keluarga yang mendesak.',
        ];
    }

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/v1/surat-pulang-cepat/pdf', $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_active_intern_can_generate_letter(): void
    {
        $user = $this->activeInternUser();

        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/surat-pulang-cepat/pdf', $this->validPayload());

        $response->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            $response->headers->get('content-type')
        );
    }

    public function test_non_intern_cannot_generate_letter(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        Sanctum::actingAs($supervisor);

        $this->postJson('/api/v1/surat-pulang-cepat/pdf', $this->validPayload())
            ->assertForbidden();
    }

    public function test_validation_fails_when_fields_missing(): void
    {
        $user = $this->activeInternUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/surat-pulang-cepat/pdf', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['early_leave_date', 'leave_time', 'reason']);
    }

    public function test_validation_fails_on_bad_time_format(): void
    {
        $user = $this->activeInternUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/surat-pulang-cepat/pdf', [
            ...$this->validPayload(),
            'leave_time' => '2.30 PM',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['leave_time']);
    }

    public function test_inactive_intern_cannot_generate_letter(): void
    {
        $user = User::factory()->create(['role' => 'intern']);
        Intern::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => Intern::STATUS_INACTIVE,
        ]);

        Sanctum::actingAs($user->fresh());

        $this->postJson('/api/v1/surat-pulang-cepat/pdf', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['early_leave_date']);
    }
}
