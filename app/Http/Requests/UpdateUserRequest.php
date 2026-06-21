<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
     * The password is optional on update: leaving it blank keeps the current
     * one, filling it resets the password. The NIK stays unique across users.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(3)],
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
