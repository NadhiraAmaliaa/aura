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

    public function test_effective_status_is_active_on_the_start_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00'));

        $intern = (new Intern)->forceFill([
            'status' => Intern::STATUS_UPCOMING,
            'start_date' => Carbon::parse('2026-06-23'),
            'end_date' => Carbon::parse('2026-07-23'),
        ]);

        // The period starts today, so the effective status must be active even
        // though the stored column still reads "upcoming".
        $this->assertSame(Intern::STATUS_ACTIVE, $intern->effectiveStatus());
        $this->assertSame(Intern::STATUS_ACTIVE, $intern->effective_status);
        $this->assertTrue($intern->isActive());
        $this->assertFalse($intern->isUpcoming());
    }

    public function test_effective_status_covers_every_boundary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00'));

        $base = [
            'start_date' => Carbon::parse('2026-06-23'),
            'end_date' => Carbon::parse('2026-07-23'),
        ];

        // Before the start date.
        $upcoming = (new Intern)->forceFill($base + ['status' => Intern::STATUS_ACTIVE]);
        $upcoming->start_date = Carbon::parse('2026-06-24');
        $this->assertSame(Intern::STATUS_UPCOMING, $upcoming->effectiveStatus());

        // On the end date is still active (inclusive).
        $endingToday = (new Intern)->forceFill([
            'status' => Intern::STATUS_UPCOMING,
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-23'),
        ]);
        $this->assertSame(Intern::STATUS_ACTIVE, $endingToday->effectiveStatus());

        // After the end date.
        $completed = (new Intern)->forceFill([
            'status' => Intern::STATUS_ACTIVE,
            'start_date' => Carbon::parse('2026-06-01'),
            'end_date' => Carbon::parse('2026-06-22'),
        ]);
        $this->assertSame(Intern::STATUS_COMPLETED, $completed->effectiveStatus());

        // A manual deactivation always wins, regardless of the calendar.
        $inactive = (new Intern)->forceFill($base + ['status' => Intern::STATUS_INACTIVE]);
        $this->assertSame(Intern::STATUS_INACTIVE, $inactive->effectiveStatus());
    }

    public function test_admin_interns_index_shows_effective_status_when_stored_column_is_stale(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00'));

        $admin = User::factory()->create(['role' => 'admin']);

        // Created while the start date was in the future, so the stored column
        // lags behind on the day the period actually begins.
        $user = $this->makeIntern([
            'status' => Intern::STATUS_UPCOMING,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.interns.index'))
            ->assertOk();

        $response->assertInertia(
            fn ($page) => $page
                ->component('admin/Interns/Index')
                ->where('interns.data.0.user_id', $user->id)
                ->where('interns.data.0.status', Intern::STATUS_UPCOMING)
                ->where('interns.data.0.effective_status', Intern::STATUS_ACTIVE)
        );
    }

    public function test_admin_interns_index_status_filter_uses_effective_status(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:00'));

        $admin = User::factory()->create(['role' => 'admin']);

        // Effectively active today but stored as "upcoming".
        $active = $this->makeIntern([
            'status' => Intern::STATUS_UPCOMING,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        // Genuinely not started yet.
        $upcoming = $this->makeIntern([
            'status' => Intern::STATUS_UPCOMING,
            'start_date' => Carbon::today()->addDays(5),
            'end_date' => Carbon::today()->addDays(30),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.interns.index', ['status' => Intern::STATUS_ACTIVE]))
            ->assertOk()
            ->assertInertia(
                fn ($page) => $page
                    ->where('interns.data', fn ($rows) => collect($rows)->pluck('user_id')->all() === [$active->id])
            );

        $this->actingAs($admin)
            ->get(route('admin.interns.index', ['status' => Intern::STATUS_UPCOMING]))
            ->assertOk()
            ->assertInertia(
                fn ($page) => $page
                    ->where('interns.data', fn ($rows) => collect($rows)->pluck('user_id')->all() === [$upcoming->id])
            );
    }

    public function test_active_intern_within_period_can_access_portal(): void
    {
        $user = $this->makeIntern();

        $this->actingAs($user)
            ->get(route('intern.dashboard'))
            ->assertOk();
    }

    public function test_inactive_intern_is_logged_out_of_portal(): void
    {
        // A manual deactivation is the only status override that blocks access
        // regardless of the calendar; the period itself is still current.
        $user = $this->makeIntern(['status' => Intern::STATUS_INACTIVE]);

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
