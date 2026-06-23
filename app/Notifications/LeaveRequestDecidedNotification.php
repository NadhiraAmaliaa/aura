<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the intern's division supervisor after a leave request has been
 * approved or rejected (status = approved | rejected).
 *
 * Currently uses the mail channel only. To add web bell notifications later,
 * append 'database' to the via() array — toArray() is already implemented.
 */
class LeaveRequestDecidedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    /**
     * Channels this notification is delivered through.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name   = $this->leaveRequest->user?->name ?? 'Peserta Magang';
        $status = $this->leaveRequest->status === 'approved' ? 'Disetujui' : 'Ditolak';

        return (new MailMessage)
            ->subject("[Pengajuan Izin {$status}] {$name} – {$this->leaveRequest->request_number}")
            ->view('mail.leave-request-decided', [
                'leaveRequest' => $this->leaveRequest,
            ]);
    }

    /**
     * Array representation — used by the database channel when added to via().
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'request_number'   => $this->leaveRequest->request_number,
            'intern_name'      => $this->leaveRequest->user?->name,
            'type'             => $this->leaveRequest->type,
            'status'           => $this->leaveRequest->status,
            'decided_by'       => $this->leaveRequest->approver?->name,
            'decided_at'       => $this->leaveRequest->approved_at?->toISOString(),
        ];
    }
}
