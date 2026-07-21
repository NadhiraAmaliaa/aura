<?php

namespace Tests\Feature\Api\V1\Leave;

use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveRequestIndexTest extends TestCase
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

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/leave-requests')->assertUnauthorized();
    }

    public function test_index_returns_own_requests_newest_first(): void
    {
        $user = $this->activeInternUser();

        LeaveRequest::factory()->for($user)->create(['created_at' => '2026-07-01 08:00']);
        LeaveRequest::factory()->for($user)->create(['created_at' => '2026-07-05 08:00']);
        LeaveRequest::factory()->for($user)->create(['created_at' => '2026-07-03 08:00']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/leave-requests')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonCount(3, 'data.items')
            ->assertJsonPath('data.items.0.created_at', Carbon::parse('2026-07-05 08:00')->toIso8601String());
    }

    public function test_index_only_returns_the_authenticated_intern_records(): void
    {
        $user = $this->activeInternUser();
        $other = $this->activeInternUser();

        LeaveRequest::factory()->for($user)->create();
        LeaveRequest::factory()->for($other)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/leave-requests')
            ->assertOk()
            ->assertJsonCount(1, 'data.items');
    }

    public function test_pending_filter_returns_only_pending(): void
    {
        $user = $this->activeInternUser();

        LeaveRequest::factory()->for($user)->create(['status' => 'pending']);
        LeaveRequest::factory()->for($user)->approved()->create();
        LeaveRequest::factory()->for($user)->rejected()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/leave-requests?filter=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.status', 'pending');
    }

    public function test_history_filter_returns_approved_and_rejected(): void
    {
        $user = $this->activeInternUser();

        LeaveRequest::factory()->for($user)->create(['status' => 'pending']);
        LeaveRequest::factory()->for($user)->approved()->create();
        LeaveRequest::factory()->for($user)->rejected()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/leave-requests?filter=history')
            ->assertOk()
            ->assertJsonCount(2, 'data.items');

        $statuses = collect($response->json('data.items'))->pluck('status')->all();
        sort($statuses);
        $this->assertSame(['approved', 'rejected'], $statuses);
    }

    public function test_resource_exposes_expected_fields(): void
    {
        $user = $this->activeInternUser();
        $approver = User::factory()->create(['role' => 'admin', 'name' => 'Pak Budi']);

        LeaveRequest::factory()->for($user)->izin()->approved($approver)->create([
            'reason' => 'Keperluan keluarga.',
            'total_days' => 1,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/leave-requests')
            ->assertOk()
            ->assertJsonPath('data.items.0.type', 'izin')
            ->assertJsonPath('data.items.0.type_label', 'Izin')
            ->assertJsonPath('data.items.0.status_label', 'Disetujui')
            ->assertJsonPath('data.items.0.approver_name', 'Pak Budi')
            ->assertJsonPath('data.items.0.can_download_pdf', true);
    }
}
