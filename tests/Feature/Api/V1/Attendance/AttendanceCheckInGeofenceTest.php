<?php

namespace Tests\Feature\Api\V1\Attendance;

use App\Models\AttendanceLocation;
use App\Models\Intern;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceCheckInGeofenceTest extends TestCase
{
    use RefreshDatabase;

    // Office location used by the "inside radius" scenario.
    private const OFFICE_LAT = 3.5952000;

    private const OFFICE_LNG = 98.6722000;

    protected function setUp(): void
    {
        parent::setUp();

        // Monday, before the 08:00 start -> an on-time WFO check-in.
        Carbon::setTestNow(Carbon::parse('2026-07-06 07:50:00'));

        WorkingHour::ensureSeeded();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function activeInternUser(): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => Intern::STATUS_ACTIVE,
        ]);

        return $user->fresh();
    }

    private function seedOffice(int $radius = 200): void
    {
        AttendanceLocation::create([
            'name' => 'Kantor Pusat',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'radius' => $radius,
            'is_active' => true,
        ]);
    }

    public function test_wfo_check_in_requires_coordinates(): void
    {
        $this->seedOffice();
        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => 'wfo',
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Lokasi Anda wajib diaktifkan untuk melakukan Check In WFO.'
            );

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_wfo_check_in_is_blocked_when_no_active_location_configured(): void
    {
        // Fail-closed: no active location at all.
        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => 'wfo',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Lokasi kantor belum dikonfigurasi. Silakan hubungi administrator.'
            );

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_wfo_check_in_is_blocked_when_inactive_location_only(): void
    {
        AttendanceLocation::create([
            'name' => 'Kantor Lama',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
            'radius' => 200,
            'is_active' => false,
        ]);

        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => 'wfo',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Lokasi kantor belum dikonfigurasi. Silakan hubungi administrator.'
            );

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_wfo_check_in_is_blocked_outside_radius(): void
    {
        $this->seedOffice(radius: 100);
        Sanctum::actingAs($this->activeInternUser());

        // ~1.1 km north of the office -> well outside the 100 m radius.
        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => 'wfo',
            'latitude' => 3.6052000,
            'longitude' => self::OFFICE_LNG,
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                fn (string $message) => str_contains($message, 'di luar radius lokasi kantor')
            );

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_wfo_check_in_succeeds_inside_radius(): void
    {
        $this->seedOffice(radius: 200);
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => 'wfo',
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
        ])
            ->assertCreated()
            ->assertJsonPath('data.work_mode', 'wfo');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_mode' => 'wfo',
        ]);
    }

    public function test_wfh_check_in_does_not_require_geofence(): void
    {
        // No active location configured, and no coordinates -> WFH still works.
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => 'wfh',
        ])
            ->assertCreated()
            ->assertJsonPath('data.work_mode', 'wfh');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_mode' => 'wfh',
        ]);
    }
}
