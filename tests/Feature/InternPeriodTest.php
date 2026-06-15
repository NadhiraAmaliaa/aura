<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InternPeriodTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Create an intern user with the given internship attributes.
     */
    private function makeIntern(array $attributes = []): User
    {
        $user = User::factory()->create(['role' => 'intern']);

        Intern::factory()->for($user)->create(array_merge([
            'status' => Intern::STATUS_ACTIVE,
            'start_date' => Carbon::today()->subDays(10),
            'end_date' => Carbon::today()->addDays(10),
        ], $attributes));

        return $user->refresh();
    }

    public function test_intern_period_and_status_helpers(): void
    {
        $intern = (new Intern)->forceFill([
            'status' => Intern::STATUS_ACTIVE,
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-30'),
        ]);

        $this->assertTrue($intern->isWithinPeriod(Carbon::parse('2026-06-15')));
        $this->assertFalse($intern->isWithinPeriod(Carbon::parse('2026-05-31')));
        $this->assertFalse($intern->isWithinPeriod(Carbon::parse('2026-07-01')));
        $this->assertTrue($intern->canRecordAttendanceOn(Carbon::parse('2026-06-30')));
        $this->assertFalse($intern->canRecordAttendanceOn(Carbon::parse('2026-07-01')));
        $this->assertTrue($intern->canAccessPortal(Carbon::parse('2026-06-30')));
        $this->assertFalse($intern->canAccessPortal(Carbon::parse('2026-07-01')));
    }

    public function test_active_intern_within_period_can_access_portal(): void
    {
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->get(route('intern.dashboard'))
            ->assertOk();
    }

    public function test_completed_intern_is_logged_out_of_portal(): void
    {
        $user = $this->makeIntern(['status' => Intern::STATUS_COMPLETED]);

        $this->actingAs($user)
            ->get(route('intern.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_intern_with_ended_period_is_logged_out_of_portal(): void
    {
        $user = $this->makeIntern([
            'start_date' => Carbon::today()->subDays(40),
            'end_date' => Carbon::today()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('intern.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_not_yet_started_intern_can_view_but_cannot_check_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 08:00'));

        $user = $this->makeIntern([
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(40),
        ]);

        // Portal is still reachable before the internship begins.
        $this->actingAs($user)
            ->get(route('intern.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('intern.attendance.check-in'), ['work_mode' => 'wfo'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_succeeds_within_period(): void
    {
        // Tuesday morning, on time.
        Carbon::setTestNow(Carbon::parse('2026-06-09 07:30'));

        $user = $this->makeIntern([
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(40),
        ]);

        $this->actingAs($user)
            ->post(route('intern.attendance.check-in'), ['work_mode' => 'wfo'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'present',
            'work_mode' => 'wfo',
        ]);
    }

    public function test_leave_request_outside_period_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));

        $user = $this->makeIntern([
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(5),
        ]);

        // End date is beyond the internship end date.
        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'izin',
                'reason' => 'Keperluan keluarga.',
                'start_date' => Carbon::today()->addDay()->toDateString(),
                'end_date' => Carbon::today()->addDays(30)->toDateString(),
            ])
            ->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_leave_request_within_period_is_accepted(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 09:00'));

        $user = $this->makeIntern([
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        $this->actingAs($user)
            ->post(route('intern.leave-requests.store'), [
                'type' => 'izin',
                'reason' => 'Keperluan keluarga.',
                'start_date' => Carbon::today()->addDay()->toDateString(),
                'end_date' => Carbon::today()->addDays(2)->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type' => 'izin',
        ]);
    }
}
