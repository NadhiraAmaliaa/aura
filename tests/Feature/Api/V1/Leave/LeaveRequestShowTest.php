<?php

namespace Tests\Feature\Api\V1\Leave;

use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveRequestShowTest extends TestCase
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

    public function test_show_requires_authentication(): void
    {
        $leaveRequest = LeaveRequest::factory()->create();

        $this->getJson("/api/v1/leave-requests/{$leaveRequest->id}")->assertUnauthorized();
    }

    public function test_owner_can_view_their_request(): void
    {
        $user = $this->activeInternUser();
        $leaveRequest = LeaveRequest::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/leave-requests/{$leaveRequest->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $leaveRequest->id)
            ->assertJsonPath('data.request_number', $leaveRequest->request_number);
    }

    public function test_intern_cannot_view_another_interns_request(): void
    {
        $user = $this->activeInternUser();
        $other = $this->activeInternUser();
        $leaveRequest = LeaveRequest::factory()->for($other)->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/leave-requests/{$leaveRequest->id}")->assertForbidden();
    }
}
