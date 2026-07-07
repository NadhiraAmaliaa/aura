<?php

namespace Tests\Feature\Api\V1\Attendance;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-06 09:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Create an active intern user for 2026 and return the user.
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

    public function test_history_requires_authentication(): void
    {
        $this->getJson('/api/v1/attendance/history')->assertUnauthorized();
    }

    public function test_history_returns_records_newest_first(): void
    {
        $user = $this->activeInternUser();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-01',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-03',
            'status' => 'late',
            'work_mode' => Attendance::WORK_MODE_WFH,
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-06',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_DINAS,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/attendance/history');

        $response->assertOk()
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.has_more', false)
            ->assertJsonCount(3, 'data.items')
            // Newest record first.
            ->assertJsonPath('data.items.0.attendance_date', '2026-07-06')
            ->assertJsonPath('data.items.1.attendance_date', '2026-07-03')
            ->assertJsonPath('data.items.2.attendance_date', '2026-07-01');
    }

    public function test_history_only_returns_the_authenticated_intern_records(): void
    {
        $user = $this->activeInternUser();
        $other = $this->activeInternUser();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-07-01',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);
        Attendance::factory()->create([
            'user_id' => $other->id,
            'attendance_date' => '2026-07-02',
            'status' => 'present',
            'work_mode' => Attendance::WORK_MODE_WFO,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/attendance/history')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.attendance_date', '2026-07-01');
    }

    public function test_history_paginates_and_reports_more_pages(): void
    {
        $user = $this->activeInternUser();

        // 20 records across distinct dates; per_page=15 -> two pages.
        for ($day = 1; $day <= 20; $day++) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_date' => Carbon::parse('2026-06-01')->addDays($day - 1)->toDateString(),
                'status' => 'present',
                'work_mode' => Attendance::WORK_MODE_WFO,
            ]);
        }

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/attendance/history')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 20)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.has_more', true)
            ->assertJsonCount(15, 'data.items');

        $this->getJson('/api/v1/attendance/history?page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.has_more', false)
            ->assertJsonCount(5, 'data.items');
    }

    public function test_history_clamps_per_page_to_the_allowed_maximum(): void
    {
        $user = $this->activeInternUser();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/attendance/history?per_page=999')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 50);
    }
}
