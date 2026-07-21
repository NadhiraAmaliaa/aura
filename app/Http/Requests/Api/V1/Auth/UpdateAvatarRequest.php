<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Mobile intern profile-photo upload request.
 *
 * The intern picks a photo from the camera or gallery; it is stored on the
 * public disk. Constraints mirror the leave-evidence upload (image types, 5 MB
 * cap) for a consistent client experience.
 */
class UpdateAvatarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The route is already guarded by `auth:sanctum`; any authenticated intern
     * may update their own photo.
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
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
            'photo' => 'foto profil',
        ];
    }
}
