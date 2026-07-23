<?php

namespace Tests\Feature\Api\V1\Device;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'fcm-registration-token-abc123';

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'android',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('device_tokens', 0);
    }

    public function test_registers_device_token_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'android',
        ])->assertNoContent();

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => self::TOKEN,
            'token_hash' => hash('sha256', self::TOKEN),
            'platform' => 'android',
        ]);
    }

    public function test_reregistering_the_same_token_upserts_without_duplicating(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'android',
        ])->assertNoContent();

        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'android',
        ])->assertNoContent();

        $this->assertDatabaseCount('device_tokens', 1);
    }

    public function test_same_token_on_a_new_account_is_repointed(): void
    {
        $first = User::factory()->create();
        Sanctum::actingAs($first);
        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'android',
        ])->assertNoContent();

        $second = User::factory()->create();
        Sanctum::actingAs($second);
        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'android',
        ])->assertNoContent();

        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertSame(
            $second->id,
            DeviceToken::where('token_hash', hash('sha256', self::TOKEN))->value('user_id'),
        );
    }

    public function test_token_is_required(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/auth/devices', ['platform' => 'android'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');
    }

    public function test_platform_must_be_supported(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/auth/devices', [
            'token' => self::TOKEN,
            'platform' => 'blackberry',
        ])->assertUnprocessable()->assertJsonValidationErrors('platform');
    }
}
