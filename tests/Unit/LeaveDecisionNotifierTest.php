<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Notifications\LeaveDecisionNotifier;
use App\Services\Notifications\PushNotificationService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class LeaveDecisionNotifierTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function makeLeaveRequest(string $type, string $status, User $user): LeaveRequest
    {
        $leaveRequest = new LeaveRequest(['type' => $type, 'status' => $status]);
        $leaveRequest->id = 5;
        $leaveRequest->request_number = 'LR-20260722-0001';
        $leaveRequest->setRelation('user', $user);

        return $leaveRequest;
    }

    public function test_builds_expected_payload_for_approved_izin(): void
    {
        $user = new User;
        $user->id = 7;

        $leaveRequest = $this->makeLeaveRequest('izin', 'approved', $user);

        $push = Mockery::mock(PushNotificationService::class);
        $push->shouldReceive('sendToUser')
            ->once()
            ->with(
                $user,
                'Pengajuan Izin disetujui',
                'Pengajuan Izin Anda (LR-20260722-0001) telah disetujui.',
                [
                    'type' => 'leave_request_decision',
                    'leave_request_id' => '5',
                    'request_number' => 'LR-20260722-0001',
                    'leave_type' => 'izin',
                    'status' => 'approved',
                ],
            );

        (new LeaveDecisionNotifier($push))->notify($leaveRequest);
    }

    public function test_builds_expected_payload_for_rejected_sakit(): void
    {
        $user = new User;
        $user->id = 8;

        $leaveRequest = $this->makeLeaveRequest('sakit', 'rejected', $user);

        $push = Mockery::mock(PushNotificationService::class);
        $push->shouldReceive('sendToUser')
            ->once()
            ->with(
                $user,
                'Pengajuan Sakit ditolak',
                'Pengajuan Sakit Anda (LR-20260722-0001) telah ditolak.',
                [
                    'type' => 'leave_request_decision',
                    'leave_request_id' => '5',
                    'request_number' => 'LR-20260722-0001',
                    'leave_type' => 'sakit',
                    'status' => 'rejected',
                ],
            );

        (new LeaveDecisionNotifier($push))->notify($leaveRequest);
    }

    public function test_does_not_notify_when_request_has_no_owner(): void
    {
        $leaveRequest = new LeaveRequest(['type' => 'izin', 'status' => 'approved']);
        $leaveRequest->id = 9;
        $leaveRequest->request_number = 'LR-20260722-0002';
        $leaveRequest->setRelation('user', null);

        $push = Mockery::mock(PushNotificationService::class);
        $push->shouldNotReceive('sendToUser');

        (new LeaveDecisionNotifier($push))->notify($leaveRequest);
    }
}
