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

    public function test_check_out_is_rejected_when_already_checked_out(): void
    {
        $user = $this->activeInternUser();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-06',
            'check_in_time' => '08:00',
            'check_out_time' => '16:00',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-out')
            ->assertStatus(409)
            ->assertJsonPath('message', 'Anda sudah melakukan Check Out hari ini.');
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
