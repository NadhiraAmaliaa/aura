<?php

namespace App\Http\Requests\Api\V1\Device;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Register (upsert) the current device's FCM registration token for the
 * authenticated intern.
 */
class StoreDeviceTokenRequest extends FormRequest
{
    /**
     * The route is already guarded by `auth:sanctum`; any authenticated intern
     * may register their own device.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', 'in:android,ios,web'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'token' => 'token perangkat',
            'platform' => 'platform',
        ];
    }
}
