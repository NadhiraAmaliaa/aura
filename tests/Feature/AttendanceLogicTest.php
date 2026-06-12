<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A weekday (Tuesday) used as the reference working day.
     */
    private function workingDayAt(string $time): Carbon
    {
        return Carbon::parse('2026-06-09 '.$time);
    }

    public function test_wfo_is_late_after_work_start(): void
    {
        $onTime = $this->workingDayAt('07:59');
        $late = $this->workingDayAt('08:30');

        $this->assertSame('present', Attendance::determineStatus($onTime, Attendance::WORK_MODE_WFO));
        $this->assertSame('late', Attendance::determineStatus($late, Attendance::WORK_MODE_WFO));
    }

    public function test_wfh_follows_late_rules(): void
    {
        $late = $this->workingDayAt('09:00');

        $this->assertSame('late', Attendance::determineStatus($late, Attendance::WORK_MODE_WFH));
    }

    public function test_dinas_is_never_late_and_always_allowed(): void
    {
        $veryLate = $this->workingDayAt('23:30');

        $this->assertSame('present', Attendance::determineStatus($veryLate, Attendance::WORK_MODE_DINAS));
        $this->assertTrue(Attendance::isCheckInAllowed($veryLate, Attendance::WORK_MODE_DINAS));
    }

    public function test_wfo_check_in_blocked_after_work_end(): void
    {
        // Tuesday work end is 17:00.
        $afterEnd = $this->workingDayAt('17:30');

        $this->assertFalse(Attendance::isCheckInAllowed($afterEnd, Attendance::WORK_MODE_WFO));
    }
}
