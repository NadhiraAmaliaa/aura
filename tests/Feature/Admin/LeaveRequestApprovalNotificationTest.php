<?php

namespace Tests\Feature\Admin;

use App\Models\Division;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Notifications\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class LeaveRequestApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a supervisor, an intern in the same division, and a pending leave
     * request of the given type owned by that intern.
     *
     * @return array{0: User, 1: User, 2: LeaveRequest}
     */
    private function makeDecisionContext(string $type): array
    {
        $division = Division::factory()->create();

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'division_id' => $division->id,
        ]);

        $internUser = User::factory()->create(['role' => 'intern']);
        Intern::factory()->for($internUser)->create([
            'division_id' => $division->id,
            'status' => Intern::STATUS_ACTIVE,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        $leaveRequest = LeaveRequest::factory()->for($internUser)->create([
            'type' => $type,
            'status' => 'pending',
        ]);

        return [$supervisor, $internUser, $leaveRequest];
    }

    public function test_approving_sends_push_notification_to_intern(): void
    {
        [$supervisor, $internUser, $leaveRequest] = $this->makeDecisionContext('izin');

        $this->mock(PushNotificationService::class, function (MockInterface $mock) use ($internUser): void {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(function (User $user, string $title, string $body, array $data) use ($internUser): bool {
                    return $user->is($internUser)
                        && $title === 'Pengajuan Izin disetujui'
                        && $data['type'] === 'leave_request_decision'
                        && $data['status'] === 'approved';
                });
        });

        $this->actingAs($supervisor)
            ->patch(route('admin.leave-requests.approve', $leaveRequest), ['admin_note' => 'Disetujui.'])
            ->assertRedirect(route('admin.leave-requests.show', $leaveRequest));

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_rejecting_sends_push_notification_to_intern(): void
    {
        [$supervisor, $internUser, $leaveRequest] = $this->makeDecisionContext('sakit');

        $this->mock(PushNotificationService::class, function (MockInterface $mock) use ($internUser): void {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->withArgs(function (User $user, string $title, string $body, array $data) use ($internUser): bool {
                    return $user->is($internUser)
                        && $title === 'Pengajuan Sakit ditolak'
                        && $data['status'] === 'rejected';
                });
        });

        $this->actingAs($supervisor)
            ->patch(route('admin.leave-requests.reject', $leaveRequest), ['admin_note' => 'Bukti tidak lengkap.'])
            ->assertRedirect(route('admin.leave-requests.show', $leaveRequest));

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function test_already_processed_request_does_not_notify(): void
    {
        [$supervisor, , $leaveRequest] = $this->makeDecisionContext('izin');
        $leaveRequest->update(['status' => 'approved']);

        $this->mock(PushNotificationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendToUser')->never();
        });

        $this->actingAs($supervisor)
            ->patch(route('admin.leave-requests.approve', $leaveRequest), []);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_push_failure_does_not_break_the_decision(): void
    {
        [$supervisor, , $leaveRequest] = $this->makeDecisionContext('izin');

        $this->mock(PushNotificationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendToUser')
                ->once()
                ->andThrow(new \RuntimeException('FCM unreachable'));
        });

        $this->actingAs($supervisor)
            ->patch(route('admin.leave-requests.approve', $leaveRequest), ['admin_note' => 'Disetujui.'])
            ->assertRedirect(route('admin.leave-requests.show', $leaveRequest));

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
        ]);
    }
}
