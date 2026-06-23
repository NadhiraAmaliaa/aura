<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only administrators manage internal staff accounts.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The NIK doubles as the username, so it is the unique login identity for
     * internal staff. A division is mandatory for supervisors (whose view is
     * scoped to it) and omitted for administrators.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('role') === 'supervisor'),
                'email',
                'max:255',
            ],
            'nik' => ['required', 'string', 'max:50', Rule::unique('users', 'nik')],
            'password' => ['required', 'confirmed', Password::min(3)],
            'role' => ['required', 'in:admin,supervisor'],
            'division_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('role') === 'supervisor'),
                'integer',
                'exists:divisions,id',
            ],
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
            // Administrators are never tied to a division.
            'division_id' => $this->input('role') === 'admin' ? null : $this->input('division_id'),
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
            'nik.unique' => 'NIK tersebut sudah terdaftar.',
            'division_id.required' => 'Divisi wajib dipilih untuk akun supervisor.',
            'email.required' => 'Email wajib diisi untuk akun supervisor.',
            'email.email' => 'Format email tidak valid.',
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
            'name' => 'nama',
            'nik' => 'NIK',
            'password' => 'kata sandi',
            'role' => 'peran',
            'division_id' => 'divisi',
        ];
    }
}
