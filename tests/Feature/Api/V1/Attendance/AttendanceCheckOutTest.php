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

class AttendanceCheckOutTest extends TestCase
{
    use RefreshDatabase;

    // Office location used by the WFO geofence scenarios.
    private const OFFICE_LAT = 3.5952000;

    private const OFFICE_LNG = 98.6722000;

    protected function setUp(): void
    {
        parent::setUp();

        // Monday, after the 17:00 end -> a normal end-of-day check-out.
        Carbon::setTestNow(Carbon::parse('2026-07-06 17:05:00'));

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

    private function checkedInToday(User $user, string $workMode = Attendance::WORK_MODE_WFO): Attendance
    {
        return Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-06',
            'check_in_time' => '08:00',
            'check_out_time' => null,
            'check_out_latitude' => null,
            'check_out_longitude' => null,
            'status' => 'present',
            'work_mode' => $workMode,
        ]);
    }

    private const EXISTING_CHECKOUT_EVENT = '99999999-9999-4999-8999-999999999999';

    private function checkedOutToday(
        User $user,
        string $checkOutTime,
        string $checkOutCapturedAt,
        string $workMode = Attendance::WORK_MODE_WFH,
    ): Attendance {
        return Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-06',
            'check_in_time' => '08:00',
            'check_out_time' => $checkOutTime,
            'check_out_captured_at' => $checkOutCapturedAt,
            'check_out_client_id' => self::EXISTING_CHECKOUT_EVENT,
            'status' => 'present',
            'work_mode' => $workMode,
        ]);
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

    public function test_check_out_requires_authentication(): void
    {
        $this->postJson('/api/v1/attendance/check-out')->assertUnauthorized();
    }

    public function test_check_out_validates_coordinates(): void
    {
        $user = $this->activeInternUser();
        $this->checkedInToday($user);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => 999,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('latitude');
    }

    public function test_check_out_records_departure_time(): void
    {
        $user = $this->activeInternUser();
        $this->checkedInToday($user);
        $this->seedOffice();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => self::OFFICE_LAT,
            'longitude' => self::OFFICE_LNG,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Check Out berhasil.')
            ->assertJsonPath('data.check_in_time', '08:00')
            ->assertJsonPath('data.check_out_time', '17:05')
            ->assertJsonPath('data.attendance_date', '2026-07-06');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'present',
        ]);
    }

    public function test_wfo_check_out_requires_coordinates(): void
    {
        $user = $this->activeInternUser();
        $this->checkedInToday($user);
        $this->seedOffice();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-out')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Lokasi Anda wajib diaktifkan untuk absensi WFO.');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_time' => null,
        ]);
    }

    public function test_wfo_check_out_is_rejected_outside_radius(): void
    {
        $user = $this->activeInternUser();
        $this->checkedInToday($user);
        $this->seedOffice(50);
        Sanctum::actingAs($user);

        // ~1.1 km north of the office, well outside the 50 m radius.
        $this->postJson('/api/v1/attendance/check-out', [
            'latitude' => self::OFFICE_LAT + 0.0100000,
            'longitude' => self::OFFICE_LNG,
        ])
            ->assertStatus(422)
            ->assertJson(fn ($json) => $json->where(
                'message',
                fn (string $message) => str_contains($message, 'di luar radius lokasi kantor')
            )->etc());

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_time' => null,
        ]);
    }

    public function test_wfh_check_out_skips_geofence(): void
    {
        $user = $this->activeInternUser();
        $this->checkedInToday($user, Attendance::WORK_MODE_WFH);
        Sanctum::actingAs($user);

        // No office configured and no coordinates: WFH must still succeed.
        $this->postJson('/api/v1/attendance/check-out')
            ->assertOk()
            ->assertJsonPath('message', 'Check Out berhasil.')
            ->assertJsonPath('data.check_out_time', '17:05');
    }

    public function test_check_out_is_rejected_without_a_check_in(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-out')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Anda harus Check In terlebih dahulu sebelum Check Out.');
    }

    public function test_check_out_overwrites_with_a_newer_captured_time(): void
    {
        $user = $this->activeInternUser();
        $this->checkedOutToday($user, '16:00', '2026-07-06 16:00:00');
        Sanctum::actingAs($user);

        // A fresh tap mints a new event id and a newer captured time -> wins.
        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-07-06 17:00:00',
            'client_event_id' => '11111111-1111-4111-8111-111111111111',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Check Out berhasil.')
            ->assertJsonPath('data.check_out_time', '17:00');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_client_id' => '11111111-1111-4111-8111-111111111111',
        ]);
    }

    public function test_check_out_does_not_move_backwards_for_an_older_event(): void
    {
        $user = $this->activeInternUser();
        $this->checkedOutToday($user, '17:00', '2026-07-06 17:00:00');
        Sanctum::actingAs($user);

        // A delayed, older event (new id but earlier capture) must be a no-op.
        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-07-06 16:30:00',
            'client_event_id' => '22222222-2222-4222-8222-222222222222',
        ])
            ->assertOk()
            ->assertJsonPath('data.check_out_time', '17:00');

        // The stored check-out is untouched: the original event still owns it.
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'check_out_client_id' => self::EXISTING_CHECKOUT_EVENT,
        ]);
    }

    public function test_replayed_check_out_with_same_event_id_is_idempotent(): void
    {
        $user = $this->activeInternUser();
        $this->checkedOutToday($user, '16:00', '2026-07-06 16:00:00');
        Sanctum::actingAs($user);

        // Same event id replay -> unchanged, even with a newer captured time.
        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-07-06 17:00:00',
            'client_event_id' => self::EXISTING_CHECKOUT_EVENT,
        ])
            ->assertOk()
            ->assertJsonPath('data.check_out_time', '16:00');
    }

    public function test_check_out_must_be_after_check_in(): void
    {
        // Now is before the recorded check-in time.
        Carbon::setTestNow(Carbon::parse('2026-07-06 07:50:00'));

        $user = $this->activeInternUser();
        $this->checkedInToday($user);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-out')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Waktu Check Out harus setelah waktu Check In.');
    }
}
