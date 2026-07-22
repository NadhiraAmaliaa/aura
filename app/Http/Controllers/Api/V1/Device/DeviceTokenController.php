<?php

namespace App\Http\Controllers\Api\V1\Device;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Device\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Http\Response;

/**
 * Registers FCM device tokens for the AURA mobile app.
 *
 * This only persists the token↔user mapping used as a future push target. It
 * deliberately contains no notification-sending logic.
 */
class DeviceTokenController extends Controller
{
    /**
     * Upsert the authenticated intern's current FCM token.
     *
     * Idempotent: keyed on the token hash so re-sending the same token (e.g. on
     * every app launch) updates the existing row instead of creating duplicates,
     * and a token that moves to a different account is re-pointed rather than
     * duplicated.
     */
    public function store(StoreDeviceTokenRequest $request): Response
    {
        $token = $request->validated('token');

        DeviceToken::updateOrCreate(
            ['token_hash' => hash('sha256', $token)],
            [
                'user_id' => $request->user()->id,
                'token' => $token,
                'platform' => $request->validated('platform'),
            ],
        );

        return response()->noContent();
    }
}
