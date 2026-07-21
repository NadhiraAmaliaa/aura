<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Shared submission logic for leave requests (izin / sakit).
 *
 * Both the web (Inertia) and mobile (API) entry points funnel through here so
 * the creation rules — evidence storage, working-day calculation and
 * supervisor notification — live in a single place.
 */
class LeaveRequestService
{
    /**
     * Create a new pending leave request for the given intern and notify their
     * division supervisor.
     *
     * The payload is expected to be already validated by the caller's form
     * request (the single source of validation rules). Evidence, when present,
     * is stored on the public disk under `leave-evidence`.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(User $user, array $data, ?UploadedFile $evidence = null): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $evidencePath = $evidence?->store('leave-evidence', 'public');

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => LeaveRequest::calculateWorkingDays($startDate, $endDate),
            'contact_phone' => $data['contact_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'evidence_path' => $evidencePath,
            'status' => 'pending',
        ]);

        $this->notifySupervisor($leaveRequest);

        return $leaveRequest;
    }

    /**
     * Find and e-mail the division supervisor for this leave request.
     *
     * Best-effort: a leave request must still succeed even if the mail server
     * is unreachable or rejects the message.
     */
    private function notifySupervisor(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->load('user.intern');

        $divisionId = $leaveRequest->user?->intern?->division_id;

        if (! $divisionId) {
            return;
        }

        $supervisor = User::where('role', 'supervisor')
            ->where('division_id', $divisionId)
            ->whereNotNull('email')
            ->where('is_active', true)
            ->first();

        if (! $supervisor) {
            return;
        }

        try {
            $supervisor->notify(new LeaveRequestSubmittedNotification($leaveRequest));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi pengajuan izin ke supervisor.', [
                'leave_request_id' => $leaveRequest->id,
                'supervisor_id' => $supervisor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
