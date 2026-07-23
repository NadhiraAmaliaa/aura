<?php

namespace App\Services\Notifications;

use App\Models\LeaveRequest;

/**
 * Builds and dispatches the push notification an intern receives once their
 * leave request (izin / sakit) has been approved or rejected.
 *
 * This isolates the (Indonesian) message wording from the controller and is the
 * only place that maps a decided leave request to a push payload.
 */
class LeaveDecisionNotifier
{
    public function __construct(private readonly PushNotificationService $push) {}

    /**
     * Notify the requesting intern about the decision on their leave request.
     *
     * Expects the request to already be decided (status = approved | rejected)
     * and its owning transaction committed. No-op when the request has no owner.
     */
    public function notify(LeaveRequest $leaveRequest): void
    {
        $user = $leaveRequest->user;

        if ($user === null) {
            return;
        }

        $typeLabel = $leaveRequest->typeLabel();
        $statusLabel = $leaveRequest->status === 'approved' ? 'disetujui' : 'ditolak';

        $title = "Pengajuan {$typeLabel} {$statusLabel}";
        $body = "Pengajuan {$typeLabel} Anda ({$leaveRequest->request_number}) telah {$statusLabel}.";

        $this->push->sendToUser($user, $title, $body, [
            'type' => 'leave_request_decision',
            'leave_request_id' => (string) $leaveRequest->id,
            'request_number' => (string) $leaveRequest->request_number,
            'leave_type' => (string) $leaveRequest->type,
            'status' => (string) $leaveRequest->status,
        ]);
    }
}
