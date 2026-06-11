<?php

namespace App\Http\Requests;

use App\Models\NonWorkingDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNonWorkingDayRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
                Rule::unique('non_working_days', 'date')->ignore($this->route('nonWorkingDay')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(NonWorkingDay::typeLabels()))],
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
            'date.required' => 'Tanggal wajib diisi.',
            'date.unique' => 'Tanggal tersebut sudah terdaftar sebagai hari libur.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis hari libur tidak valid.',
        ];
    }
}
