<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Mobile intern change-password request.
 *
 * Mirrors the web {@see PasswordController}: the
 * current password must be confirmed, and the new password uses the same
 * minimum strength (`Password::min(3)`) applied when interns are created.
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(3)],
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
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ];
    }
}
