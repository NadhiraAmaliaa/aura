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
