<?php

namespace App\Http\Resources\Api\V1\Attendance;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a single attendance record.
 *
 * Shared by the dashboard (today's record) and, later, the history list. Times
 * are emitted as `H:i` strings and coordinates as nullable decimals so the
 * mobile client can render them without re-parsing Carbon casts.
 *
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
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
            'attendance_date' => $this->attendance_date?->toDateString(),
            'check_in_time' => $this->check_in_time?->format('H:i'),
            'check_out_time' => $this->check_out_time?->format('H:i'),
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'work_mode' => $this->work_mode,
            'work_mode_label' => $this->workModeLabel(),
        ];
    }
}
