<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreInternRequest extends FormRequest
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
     * The intern login identity is the (university, NIM) pair, so the NIM is
     * validated for uniqueness within the selected university only. Interns do
     * not use an email address.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(3)],
            'intern_program_id' => ['required', 'exists:intern_programs,id'],
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'study_program_id' => [
                'required',
                'integer',
                Rule::exists('study_programs', 'id')->where(function ($query) {
                    // The study program must belong to the chosen university.
                    $query->where('university_id', $this->integer('university_id'));
                }),
            ],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'nim' => [
                'required',
                'string',
                'max:50',
                Rule::unique('interns', 'nim')->where(
                    fn ($query) => $query->where('university_id', $this->integer('university_id'))
                ),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
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
            'nim.unique' => 'NIM tersebut sudah terdaftar pada universitas yang dipilih.',
            'study_program_id.exists' => 'Program studi tidak valid untuk universitas yang dipilih.',
        ];
    }

    /**
     * Custom attribute names for clearer validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'intern_program_id' => 'program magang',
            'university_id' => 'universitas',
            'study_program_id' => 'program studi',
            'division_id' => 'divisi',
            'nim' => 'NIM',
            'phone' => 'nomor telepon',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
        ];
    }
}
