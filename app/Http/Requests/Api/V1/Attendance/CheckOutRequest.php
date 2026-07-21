<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a mobile check-out: optional GPS coordinates only. The work mode
 * was already fixed at check-in, so it is not accepted here.
 */
class CheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isIntern() ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            // Offline queue fields. All optional; see CheckInRequest.
            'captured_at' => ['nullable', 'date'],
            'client_event_id' => ['nullable', 'string', 'uuid'],
            'office_id' => ['nullable', 'integer'],
            // Frozen office geofence snapshot captured on the device; see
            // CheckInRequest.
            'office_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'office_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'office_radius' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'office_name' => ['nullable', 'string', 'max:255'],
            'auto_time_enabled' => ['nullable', 'boolean'],
        ];
    }
}
