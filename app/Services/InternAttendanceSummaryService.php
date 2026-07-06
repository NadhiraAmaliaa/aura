<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\NonWorkingDay;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-only dashboard data for a single intern's attendance.
 *
 * Produces the two pieces the mobile attendance dashboard needs:
 *  - the "today" snapshot (working hours, today's record and any approved
 *    leave covering today), and
 *  - a monthly recap (present / late / permission / sick / dinas / absent).
 *
 * All attendance business rules (working days, non-working days, late status)
 * live in the models; this service only reads and aggregates them. It performs
 * a single in-memory pass over the month using pre-fetched maps to avoid N+1
 * queries, mirroring {@see AttendanceReportService}.
 */
class InternAttendanceSummaryService
{
    /**
     * Build the today snapshot for the user.
     *
     * @return array<string, mixed>
     */
    public function todaySnapshot(User $user): array
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $leave = LeaveRequest::approvedCovering($user->id, $today)->first();

        return [
            'date' => $today->toDateString(),
            'is_working_day' => ! Attendance::isNonWorkingDay($today),
            'work_hours' => [
                'start' => WorkingHour::startTimeFor($today),
                'end' => WorkingHour::endTimeFor($today),
            ],
            'attendance' => $attendance,
            'leave' => $leave === null ? null : [
                'type' => $leave->type,
                'type_label' => $leave->typeLabel(),
            ],
        ];
    }

    /**
     * Build the monthly attendance recap for the user.
     *
     * Counts are derived by walking every day of the month once. Working days
     * outside the intern's active period are ignored; future working days with
     * no record yet are not counted as absent.
     *
     * @return array<string, mixed>
     */
    public function monthlySummary(User $user, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $today = Carbon::today();

        $intern = $user->intern;

        $attendances = $this->attendancesByDate($user->id, $start, $end);
        $leaves = $this->approvedLeaves($user->id, $start, $end);
        $holidays = $this->holidaySet($start, $end);

        $counts = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'dinas' => 0,
            'tidak_absen' => 0,
        ];

        for ($day = $start->copy(); $day->lessThanOrEqualTo($end); $day->addDay()) {
            if (! $this->isWorkingDay($day, $holidays)) {
                continue;
            }

            if ($intern !== null && ! $intern->isWithinPeriod($day)) {
                continue;
            }

            $attendance = $attendances->get($day->toDateString());
            $leave = $this->leaveCovering($leaves, $day);

            if ($attendance !== null && $attendance->check_in_time !== null) {
                $counts['hadir']++;

                if ($attendance->status === 'late') {
                    $counts['terlambat']++;
                }

                if ($attendance->work_mode === Attendance::WORK_MODE_DINAS) {
                    $counts['dinas']++;
                }

                continue;
            }

            if ($leave !== null) {
                if ($leave->type === 'sakit') {
                    $counts['sakit']++;
                } else {
                    $counts['izin']++;
                }

                continue;
            }

            // Only elapsed working days can count as an absence.
            if ($day->lessThanOrEqualTo($today)) {
                $counts['tidak_absen']++;
            }
        }

        return array_merge(['month' => $start->format('Y-m')], $counts);
    }

    /**
     * Attendances for the user in the range, keyed by date string.
     *
     * @return Collection<string, Attendance>
     */
    private function attendancesByDate(int $userId, Carbon $start, Carbon $end): Collection
    {
        return Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $a): string => $a->attendance_date->toDateString());
    }

    /**
     * Approved leave requests overlapping the range for the user.
     *
     * @return Collection<int, LeaveRequest>
     */
    private function approvedLeaves(int $userId, Carbon $start, Carbon $end): Collection
    {
        return LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();
    }

    /**
     * Registered non-working days in the range, as a set of date strings.
     *
     * @return Collection<string, string>
     */
    private function holidaySet(Carbon $start, Carbon $end): Collection
    {
        return NonWorkingDay::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->flip();
    }

    /**
     * Whether a day is a working day (configured working day and not a holiday).
     *
     * @param  Collection<string, string>  $holidays
     */
    private function isWorkingDay(Carbon $day, Collection $holidays): bool
    {
        return WorkingHour::isWorkingDay($day) && ! $holidays->has($day->toDateString());
    }

    /**
     * The approved leave that covers the given day, or null.
     *
     * @param  Collection<int, LeaveRequest>  $leaves
     */
    private function leaveCovering(Collection $leaves, Carbon $day): ?LeaveRequest
    {
        return $leaves->first(function (LeaveRequest $leave) use ($day): bool {
            return $day->betweenIncluded($leave->start_date, $leave->end_date);
        });
    }
}
