<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingHourRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_working_day' => ['boolean'],
            'start_time' => ['nullable', 'required_if:is_working_day,true', 'date_format:H:i'],
            'end_time' => ['nullable', 'required_if:is_working_day,true', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * When a day is non-working its times are cleared so they are stored as
     * null and never trigger the working-day-only rules.
     */
    protected function prepareForValidation(): void
    {
        $isWorkingDay = $this->boolean('is_working_day');

        $this->merge([
            'is_working_day' => $isWorkingDay,
            'start_time' => $isWorkingDay ? $this->input('start_time') : null,
            'end_time' => $isWorkingDay ? $this->input('end_time') : null,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_time.required_if' => 'Jam masuk wajib diisi untuk hari kerja.',
            'start_time.date_format' => 'Format jam masuk tidak valid (HH:MM).',
            'end_time.required_if' => 'Jam pulang wajib diisi untuk hari kerja.',
            'end_time.date_format' => 'Format jam pulang tidak valid (HH:MM).',
            'end_time.after' => 'Jam pulang harus setelah jam masuk.',
        ];
    }
}
