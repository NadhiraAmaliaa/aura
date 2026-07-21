<?php

namespace App\Http\Resources\Api\V1\Leave;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a single leave request (izin / sakit).
 *
 * Dates are emitted as `Y-m-d` strings and labels are pre-resolved to their
 * Indonesian form so the mobile client renders them without re-parsing casts.
 * `evidence_url` is the public-disk URL of the uploaded attachment (or null).
 * `can_download_pdf` mirrors the backend rule that only approved requests have
 * a printable letter.
 *
 * @mixin LeaveRequest
 */
class LeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'reason' => $this->reason,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => $this->total_days,
            'contact_phone' => $this->contact_phone,
            'address' => $this->address,
            'evidence_url' => $this->evidence_url,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'admin_note' => $this->admin_note,
            'approver_name' => $this->whenLoaded('approver', fn () => $this->approver?->name),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'can_download_pdf' => $this->status === 'approved',
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
