<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isIntern() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:izin,sakit'],
            'reason' => ['required', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Jenis pengajuan wajib dipilih.',
            'type.in' => 'Jenis pengajuan tidak valid.',
            'reason.required' => 'Alasan wajib diisi.',
            'start_date.required' => 'Tanggal awal wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal awal tidak boleh di masa lalu.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
        ];
    }

    /**
     * Ensure the leave falls inside the intern's active internship period.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $intern = $this->user()?->intern;

            if ($intern === null) {
                $validator->errors()->add('start_date', 'Profil magang Anda belum lengkap. Silakan hubungi administrator.');

                return;
            }

            if (! $intern->canSubmitLeave()) {
                $validator->errors()->add(
                    'start_date',
                    'Masa magang Anda tidak aktif, sehingga tidak dapat mengajukan izin/sakit.'
                );

                return;
            }

            $startDate = $this->date('start_date');
            $endDate = $this->date('end_date');

            if ($startDate && $intern->start_date && $startDate->lt($intern->start_date->copy()->startOfDay())) {
                $validator->errors()->add(
                    'start_date',
                    'Tanggal awal berada di luar masa magang Anda (mulai '.$intern->start_date->format('d-m-Y').').'
                );
            }

            if ($endDate && $intern->end_date && $endDate->gt($intern->end_date->copy()->startOfDay())) {
                $validator->errors()->add(
                    'end_date',
                    'Tanggal akhir berada di luar masa magang Anda (berakhir '.$intern->end_date->format('d-m-Y').').'
                );
            }
        });
    }
}
