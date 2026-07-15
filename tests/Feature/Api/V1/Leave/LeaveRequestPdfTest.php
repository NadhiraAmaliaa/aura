<?php

namespace Tests\Feature\Api\V1\Leave;

use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveRequestPdfTest extends TestCase
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

    public function test_pdf_requires_authentication(): void
    {
        $leaveRequest = LeaveRequest::factory()->approved()->create();

        $this->getJson("/api/v1/leave-requests/{$leaveRequest->id}/pdf")->assertUnauthorized();
    }

    public function test_owner_can_download_approved_pdf(): void
    {
        $user = $this->activeInternUser();
        $leaveRequest = LeaveRequest::factory()->for($user)->approved()->create();

        Sanctum::actingAs($user);

        $response = $this->get("/api/v1/leave-requests/{$leaveRequest->id}/pdf");

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_pending_request_has_no_pdf(): void
    {
        $user = $this->activeInternUser();
        $leaveRequest = LeaveRequest::factory()->for($user)->create(['status' => 'pending']);

        Sanctum::actingAs($user);

        $this->get("/api/v1/leave-requests/{$leaveRequest->id}/pdf")->assertForbidden();
    }

    public function test_intern_cannot_download_another_interns_pdf(): void
    {
        $user = $this->activeInternUser();
        $other = $this->activeInternUser();
        $leaveRequest = LeaveRequest::factory()->for($other)->approved()->create();

        Sanctum::actingAs($user);

        $this->get("/api/v1/leave-requests/{$leaveRequest->id}/pdf")->assertForbidden();
    }
}
