<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudyProgramRequest extends FormRequest
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
     * A study program must always belong to a university. Its name is unique
     * within that university only.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('study_programs', 'name')->where(
                    fn ($query) => $query->where('university_id', $this->integer('university_id'))
                ),
            ],
            'level' => ['nullable', 'string', 'max:20'],
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
            'university_id.required' => 'Perguruan tinggi wajib dipilih.',
            'university_id.exists' => 'Perguruan tinggi tidak valid.',
            'name.required' => 'Nama program studi wajib diisi.',
            'name.unique' => 'Program studi tersebut sudah terdaftar pada perguruan tinggi ini.',
        ];
    }
}
