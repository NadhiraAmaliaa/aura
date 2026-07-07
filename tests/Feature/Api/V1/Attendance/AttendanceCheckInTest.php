<?php

namespace Tests\Feature\Api\V1\Attendance;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceCheckInTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_check_in_requires_authentication(): void
    {
        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
        ])->assertUnauthorized();
    }

    public function test_check_in_validates_work_mode(): void
    {
        Sanctum::actingAs($this->activeInternUser());

        $this->postJson('/api/v1/attendance/check-in', [])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('work_mode');
    }

    public function test_check_in_records_an_on_time_present_status(): void
    {
        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
            'latitude' => 3.5952000,
            'longitude' => 98.6722000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Check In berhasil.')
            ->assertJsonPath('data.status', 'present')
            ->assertJsonPath('data.status_label', 'Hadir')
            ->assertJsonPath('data.work_mode', 'wfo')
            ->assertJsonPath('data.check_in_time', '07:50')
            ->assertJsonPath('data.attendance_date', '2026-07-06');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'present',
            'work_mode' => 'wfo',
        ]);
    }

    public function test_check_in_after_start_time_is_late(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-06 08:30:00'));

        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'late');
    }

    public function test_check_in_is_rejected_when_already_checked_in(): void
    {
        $user = $this->activeInternUser();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-06',
            'check_in_time' => '07:40',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Anda sudah melakukan Check In hari ini.');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_check_in_is_rejected_during_approved_leave(): void
    {
        $user = $this->activeInternUser();
        LeaveRequest::create([
            'user_id' => $user->id,
            'type' => 'izin',
            'reason' => 'Urusan keluarga',
            'status' => 'approved',
            'start_date' => '2026-07-06',
            'end_date' => '2026-07-06',
            'total_days' => 1,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'tidak dapat melakukan Check In'));

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_is_rejected_after_the_deadline(): void
    {
        // Past the 17:00 WFO cut-off on a working day.
        Carbon::setTestNow(Carbon::parse('2026-07-06 18:00:00'));

        $user = $this->activeInternUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/attendance/check-in', [
            'work_mode' => Attendance::WORK_MODE_WFO,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'terlewati'));

        $this->assertDatabaseCount('attendances', 0);
    }
}
