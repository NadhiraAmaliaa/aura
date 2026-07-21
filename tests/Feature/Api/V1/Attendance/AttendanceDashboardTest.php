<?php

namespace Tests\Feature\Api\V1\Attendance;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze "today" to a known Monday so the recap and today snapshot are
        // deterministic.
        Carbon::setTestNow(Carbon::parse('2026-07-06 09:00:00'));

        WorkingHour::ensureSeeded();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Create an active intern user for July 2026 and return the user.
     */
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

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/v1/attendance/dashboard')->assertUnauthorized();
    }

    public function test_dashboard_returns_today_snapshot_and_monthly_recap(): void
    {
        $user = $this->activeInternUser();

        // July 2026 working days: 1 Wed, 2 Thu, 3 Fri, 6 Mon.
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-01',
            'check_in_time' => '07:50',
            'check_out_time' => '17:00',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-02',
            'check_in_time' => '08:30',
            'check_out_time' => '17:00',
            'status' => 'late',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-03',
            'check_in_time' => '09:00',
            'check_out_time' => '15:00',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_DINAS,
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-06',
            'check_in_time' => '07:45',
            'check_out_time' => null,
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/attendance/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.today.date', '2026-07-06')
            ->assertJsonPath('data.today.is_working_day', true)
            ->assertJsonPath('data.today.work_hours.start', '08:00')
            ->assertJsonPath('data.today.work_hours.end', '17:00')
            ->assertJsonPath('data.today.attendance.check_in_time', '07:45')
            ->assertJsonPath('data.today.attendance.status', 'present')
            ->assertJsonPath('data.today.leave', null)
            ->assertJsonPath('data.summary.month', '2026-07')
            ->assertJsonPath('data.summary.hadir', 4)
            ->assertJsonPath('data.summary.terlambat', 1)
            ->assertJsonPath('data.summary.dinas', 1)
            ->assertJsonPath('data.summary.izin', 0)
            ->assertJsonPath('data.summary.sakit', 0)
            ->assertJsonPath('data.summary.tidak_absen', 0);
    }

    public function test_dashboard_counts_elapsed_working_days_without_record_as_absent(): void
    {
        $user = $this->activeInternUser();

        // Only one of the four elapsed July working days has a record.
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-01',
            'check_in_time' => '07:50',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/attendance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.summary.hadir', 1)
            // 2, 3 and 6 July are elapsed working days with no record.
            ->assertJsonPath('data.summary.tidak_absen', 3);
    }
}
