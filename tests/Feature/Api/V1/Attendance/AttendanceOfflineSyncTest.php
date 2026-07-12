<?php

namespace Tests\Feature\Api\V1\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Intern;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Covers the mobile offline attendance queue: captured_at is authoritative for
 * the date/time/status, client_event_id makes a replayed sync idempotent, and
 * the captured office ties the geofence check to a specific location. Online /
 * web callers that omit these fields keep the original server-clock behaviour.
 *
 * The app runs in the Asia/Jakarta timezone, so captured_at values here are
 * naive local wall-clock strings, matching the Carbon::setTestNow convention
 * used across the attendance suite. A real client sends an ISO-8601 string with
 * the +07:00 offset, which Carbon parses to the same instant.
 */
class AttendanceOfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    private const NEAR_LAT = 3.5952000;
    private const NEAR_LNG = 98.6722000;

    private const EVENT_A = '11111111-1111-4111-8111-111111111111';
    private const EVENT_B = '22222222-2222-4222-8222-222222222222';

    private int $nearOfficeId;

    protected function setUp(): void
    {
        parent::setUp();

        // Monday, before the 08:00 start.
        Carbon::setTestNow(Carbon::parse('2026-07-06 07:50:00'));

        WorkingHour::ensureSeeded();

        $office = AttendanceLocation::create([
            'name' => 'Kantor Pusat',
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'radius' => 200,
            'is_active' => true,
        ]);

        $this->nearOfficeId = $office->id;
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

    public function test_captured_at_drives_date_time_and_status_even_when_synced_later(): void
    {
        // Captured on-time at 07:55, but only synced at 08:20.
        Carbon::setTestNow(Carbon::parse('2026-07-06 08:20:00'));

        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:55:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
            'office_latitude' => self::NEAR_LAT,
            'office_longitude' => self::NEAR_LNG,
            'office_radius' => 200,
            'office_name' => 'Kantor Pusat',
            'auto_time_enabled' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'present')
            ->assertJsonPath('data.check_in_time', '07:55')
            ->assertJsonPath('data.attendance_date', '2026-07-06');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_in_client_id' => self::EVENT_A,
            'check_in_captured_at' => '2026-07-06 07:55:00',
            'check_in_office_id' => $this->nearOfficeId,
            'check_in_auto_time' => true,
            'status' => 'present',
        ]);

        // synced_at was recorded (audit) and reflects the later sync time, and
        // the office geofence snapshot was frozen onto the record.
        $attendance = Attendance::where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($attendance->check_in_synced_at);
        $this->assertSame('2026-07-06 08:20:00', $attendance->check_in_synced_at->format('Y-m-d H:i:s'));
        $this->assertSame('3.5952000', $attendance->check_in_office_latitude);
        $this->assertSame('98.6722000', $attendance->check_in_office_longitude);
        $this->assertSame(200, $attendance->check_in_office_radius);
        $this->assertSame('Kantor Pusat', $attendance->check_in_office_name);
    }

    public function test_captured_after_deadline_is_late(): void
    {
        // Captured at 08:30 (late), synced at 09:00.
        Carbon::setTestNow(Carbon::parse('2026-07-06 09:00:00'));

        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T08:30:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'late')
            ->assertJsonPath('data.check_in_time', '08:30');
    }

    public function test_replayed_check_in_is_idempotent(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $payload = [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:45:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ];

        $first = $this->postJson('/api/v1/attendance/check-in', $payload)->assertCreated();

        // A retried sync of the same event returns the existing record as 200
        // (already done) rather than a 409 or a duplicate.
        $second = $this->postJson('/api/v1/attendance/check-in', $payload)->assertOk();

        $this->assertSame(
            $first->json('data.id'),
            $second->json('data.id'),
        );
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_second_check_in_same_day_with_different_event_conflicts(): void
    {
        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:45:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ])->assertCreated();

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:48:00',
            'client_event_id' => self::EVENT_B,
            'office_id' => $this->nearOfficeId,
        ])->assertStatus(409);
    }

    public function test_captured_at_in_the_future_is_rejected(): void
    {
        // Now is 07:50; a capture at 09:00 is implausibly in the future.
        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T09:00:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ])->assertStatus(422);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_captured_at_older_than_retention_window_is_rejected(): void
    {
        // Six days old exceeds the 5-day offline retention window.
        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-06-30T07:50:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ])->assertStatus(422);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_geofence_validates_against_the_captured_office(): void
    {
        // A second, far-away active office. The device is standing at the near
        // office coordinates but claims it captured against the far office, so
        // validation against that specific office must fail.
        $farOffice = AttendanceLocation::create([
            'name' => 'Kantor Cabang',
            'latitude' => 3.7000000,
            'longitude' => 98.6722000,
            'radius' => 200,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:45:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $farOffice->id,
        ])->assertStatus(422);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_online_check_in_without_captured_at_uses_server_clock(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
        ])
            ->assertCreated()
            ->assertJsonPath('data.check_in_time', '07:50');

        // No offline metadata is stored for a plain online check-in, but the
        // live office geofence config is snapshotted onto the record.
        $attendance = Attendance::where('user_id', $user->id)->firstOrFail();
        $this->assertNull($attendance->check_in_captured_at);
        $this->assertNull($attendance->check_in_synced_at);
        $this->assertNull($attendance->check_in_client_id);
        $this->assertSame($this->nearOfficeId, $attendance->check_in_office_id);
        $this->assertSame('3.5952000', $attendance->check_in_office_latitude);
        $this->assertSame(200, $attendance->check_in_office_radius);
        $this->assertSame('Kantor Pusat', $attendance->check_in_office_name);
    }

    public function test_offline_snapshot_geofence_beats_a_later_live_config_change(): void
    {
        // The office was later moved/shrunk AND renamed online.
        AttendanceLocation::whereKey($this->nearOfficeId)->update([
            'radius' => 50,
            'name' => 'Kantor Pusat (Pindah)',
        ]);

        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        // Captured ~167 m north of the office. The frozen snapshot radius (300 m)
        // accepts it; the current live radius (50 m) would have rejected it. The
        // frozen name is retained even though the live office was renamed.
        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => 3.5967000,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:45:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
            'office_latitude' => self::NEAR_LAT,
            'office_longitude' => self::NEAR_LNG,
            'office_radius' => 300,
            'office_name' => 'Kantor Pusat',
        ])->assertCreated();

        $attendance = Attendance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(300, $attendance->check_in_office_radius);
        $this->assertSame('Kantor Pusat', $attendance->check_in_office_name);
    }

    public function test_offline_snapshot_rejects_when_outside_the_frozen_radius(): void
    {
        // The office was later widened online to cover a huge radius.
        AttendanceLocation::whereKey($this->nearOfficeId)->update(['radius' => 100000]);

        Sanctum::actingAs($this->activeInternUser());

        // Captured ~556 m north. The frozen snapshot radius (200 m) rejects it,
        // even though the current live radius (100 km) would have accepted it.
        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => 3.6002000,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T07:45:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
            'office_latitude' => self::NEAR_LAT,
            'office_longitude' => self::NEAR_LNG,
            'office_radius' => 200,
        ])->assertStatus(422);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_out_uses_captured_at(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        // Online check-in in the morning.
        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
        ])->assertCreated();

        // Check-out captured at 17:00 but synced at 17:30.
        Carbon::setTestNow(Carbon::parse('2026-07-06 17:30:00'));

        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T17:00:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
            'office_latitude' => self::NEAR_LAT,
            'office_longitude' => self::NEAR_LNG,
            'office_radius' => 200,
            'office_name' => 'Kantor Pusat',
        ])
            ->assertOk()
            ->assertJsonPath('data.check_out_time', '17:00');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_client_id' => self::EVENT_A,
            'check_out_captured_at' => '2026-07-06 17:00:00',
            'check_out_office_id' => $this->nearOfficeId,
        ]);

        $attendance = Attendance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('3.5952000', $attendance->check_out_office_latitude);
        $this->assertSame(200, $attendance->check_out_office_radius);
        $this->assertSame('Kantor Pusat', $attendance->check_out_office_name);
    }

    public function test_replayed_check_out_is_idempotent(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
        ])->assertCreated();

        Carbon::setTestNow(Carbon::parse('2026-07-06 17:30:00'));

        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T17:00:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ])->assertOk()->assertJsonPath('data.check_out_time', '17:00');

        // Replaying the same event with a different captured time returns the
        // original record unchanged.
        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => self::NEAR_LAT,
            'longitude' => self::NEAR_LNG,
            'captured_at' => '2026-07-06T17:15:00',
            'client_event_id' => self::EVENT_A,
            'office_id' => $this->nearOfficeId,
        ])->assertOk()->assertJsonPath('data.check_out_time', '17:00');

        $attendance = Attendance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('17:00', $attendance->check_out_time->format('H:i'));
    }
}
