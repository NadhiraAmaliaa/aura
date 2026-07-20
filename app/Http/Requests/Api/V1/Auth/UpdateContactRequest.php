<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Mobile intern contact-details update request.
 *
 * Mirrors the intern branch of the web {@see ProfileUpdateRequest}:
 * the email lives on the user account while the phone belongs to the intern
 * profile. Both stay optional to match the existing web behaviour.
 */
class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The route is already guarded by `auth:sanctum`; any authenticated intern
     * may edit their own contact details.
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
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
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
            'email' => 'email',
            'phone' => 'nomor HP',
        ];
    }
}
