<?php

namespace App\Http\Requests\Api\V1\Attendance;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a mobile check-in: the chosen work mode plus optional GPS
 * coordinates. Mirrors the web `CheckInRequest` rules so both entry points
 * accept the same payload.
 */
class CheckInRequest extends FormRequest
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
            'work_mode' => ['required', Rule::in(array_keys(Attendance::workModeLabels()))],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            // Offline queue fields. All optional: an immediate online check-in
            // omits them and the server uses its own clock.
            'captured_at' => ['nullable', 'date'],
            'client_event_id' => ['nullable', 'string', 'uuid'],
            'office_id' => ['nullable', 'integer'],
            // Frozen office geofence snapshot captured on the device. Bounded to
            // the same radius ceiling the admin form allows so a tampered client
            // cannot widen its own geofence.
            'office_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'office_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'office_radius' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'office_name' => ['nullable', 'string', 'max:255'],
            'auto_time_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'work_mode.required' => 'Mode kehadiran wajib dipilih.',
            'work_mode.in' => 'Mode kehadiran tidak valid.',
        ];
    }
}
