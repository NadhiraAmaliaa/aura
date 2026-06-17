<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Admins authenticate with their NIK, so it is required and must stay
     * unique. Interns sign in through their university + NIM and never have a
     * NIK, so the field is omitted from their profile form entirely.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                Rule::requiredIf(fn (): bool => $this->user()->isAdmin()),
                'nullable',
                'string',
                'max:50',
                Rule::unique(User::class, 'nik')->ignore($this->user()->id),
            ],
        ];
    }
}
