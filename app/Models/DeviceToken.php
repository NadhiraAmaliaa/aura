<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Firebase Cloud Messaging (FCM) registration token belonging to one intern's
 * device. Used later as a push-notification target; no sending logic yet.
 */
#[Fillable(['user_id', 'token', 'token_hash', 'platform'])]
class DeviceToken extends Model
{
    /**
     * The intern that owns this device token.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
