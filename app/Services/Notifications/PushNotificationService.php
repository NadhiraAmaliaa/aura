<?php

namespace App\Services\Notifications;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

/**
 * Reusable Firebase Cloud Messaging (FCM) sender built on top of the existing
 * `device_tokens` table.
 *
 * Delivery is strictly best-effort: a caller's business operation must never
 * fail because a push could not be sent (unreachable FCM, missing credentials,
 * expired tokens, ...). Every failure is swallowed and logged, and tokens that
 * FCM reports as invalid are pruned so the table self-heals over time.
 */
class PushNotificationService
{
    public function __construct(private readonly Messaging $messaging) {}

    /**
     * Push a notification to every device registered by the given user.
     *
     * @param  array<string, string>  $data  Optional data payload (string values only, per FCM).
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = $user->deviceTokens()->pluck('token')->all();

        if ($tokens === []) {
            return;
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try {
            $report = $this->messaging->sendMulticast($message, $tokens);
        } catch (Throwable $e) {
            Log::error('Gagal mengirim push notification.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $this->pruneInvalidTokens($report->invalidTokens());
    }

    /**
     * Remove tokens that FCM no longer recognises so we stop targeting them.
     *
     * @param  array<int, string>  $tokens
     */
    private function pruneInvalidTokens(array $tokens): void
    {
        if ($tokens === []) {
            return;
        }

        DeviceToken::whereIn('token', $tokens)->delete();
    }
}
