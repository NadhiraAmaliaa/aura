<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSuratPulangCepatRequest extends FormRequest
{
    /**
     * Only an intern may generate their own early-leave letter.
     */
    public function authorize(): bool
    {
        return $this->user()?->isIntern() ?? false;
    }

    /**
     * Validation rules for the early-leave letter.
     *
     * Deliberately light — the letter is stateless and its field set is still
     * being confirmed with SDM. The intern's identity is taken from the
     * authenticated account, not the request body.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'early_leave_date' => ['required', 'date'],
            'leave_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Indonesian validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'early_leave_date.required' => 'Tanggal pulang wajib diisi.',
            'early_leave_date.date' => 'Tanggal pulang tidak valid.',
            'leave_time.required' => 'Jam pulang wajib diisi.',
            'leave_time.date_format' => 'Jam pulang harus dalam format JJ:MM (contoh 14:30).',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.max' => 'Alasan maksimal 1000 karakter.',
        ];
    }

    /**
     * Ensure the intern profile exists and the internship is still active.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $intern = $this->user()?->intern;

            if ($intern === null) {
                $validator->errors()->add(
                    'early_leave_date',
                    'Profil magang Anda belum lengkap. Silakan hubungi administrator.'
                );

                return;
            }

            if (! $intern->canSubmitLeave()) {
                $validator->errors()->add(
                    'early_leave_date',
                    'Masa magang Anda tidak aktif, sehingga tidak dapat membuat surat.'
                );
            }
        });
    }
}
