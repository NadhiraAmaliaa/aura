<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store per-device Firebase Cloud Messaging (FCM) registration tokens.
     *
     * Each row maps an FCM token to the intern that registered it, so the
     * backend can later target push notifications at a specific device. The
     * mobile client upserts its current token after login and whenever FCM
     * rotates it.
     */
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The raw FCM token. Kept un-indexed because it can exceed the
            // unique-index key-length limit on some databases (e.g. SQL Server's
            // 900-byte cap): uniqueness is enforced on the fixed-width hash
            // below instead.
            $table->string('token', 512);
            // SHA-256 hex digest of the token (64 chars). This is what the
            // upsert keys on, giving a stable, cross-database unique constraint
            // regardless of how long the raw token is.
            $table->char('token_hash', 64)->unique();
            $table->string('platform', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
