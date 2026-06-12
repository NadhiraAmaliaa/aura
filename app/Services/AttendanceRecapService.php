<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\NonWorkingDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds attendance recap data over a reporting period.
 *
 * The recap is based on "effective working days" (the selected period minus
 * weekends and registered non-working days). Only attendance records that fall
 * on effective working days are counted; records stored on weekends or
 * non-working days remain untouched but are excluded from every metric.
 *
 * The service returns plain, structured data so it can later be reused by
 * export features (Excel, PDF) without duplicating the calculation logic.
 */
class AttendanceRecapService
{
    /**
     * Attendance statuses that count as physically/remotely working
     * (present or late), regardless of the work mode (WFO / WFH / Dinas).
     *
     * @var array<int, string>
     */
    private const WORKING_STATUSES = ['present', 'late'];

    /**
     * Attendance statuses that count as an approved absence (Izin).
     *
     * @var array<int, string>
     */
    private const LEAVE_STATUSES = ['permission', 'sick'];

    /**
     * Build the recap for the given period.
     *
     * @return array{
     *     start_date: Carbon,
     *     end_date: Carbon,
     *     effective_working_days: int,
     *     rows: array<int, array<string, mixed>>
     * }
     */
    public function build(Carbon $startDate, Carbon $endDate, ?int $programId = null): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        // All effective working dates within the report period (weekends and
        // registered non-working days removed). This is the shared calendar;
        // each intern's effective days are a subset bounded by their own
        // internship start/end dates.
        $effectiveDates = $this->effectiveWorkingDates($start, $end);

        $todayString = Carbon::today()->toDateString();

        $interns = $this->interns($programId);
        $attendancesByUser = $this->attendancesByUser($interns->pluck('user_id')->all(), $start, $end);

        $rows = [];

        foreach ($interns as $intern) {
            // Restrict the shared calendar to the dates this intern is actually
            // an active participant, so each intern gets their own EWD.
            $internDates = $this->datesWithinInternship($effectiveDates, $intern);
            $internLookup = array_flip($internDates);

            $effectiveWorkingDays = count($internDates);
            $completedEffectiveDays = count(array_filter(
                $internDates,
                fn (string $date): bool => $date < $todayString
            ));

            $records = ($attendancesByUser->get($intern->user_id) ?? collect())
                ->filter(fn (Attendance $attendance): bool => isset(
                    $internLookup[$attendance->attendance_date->toDateString()]
                ));

            $rows[] = $this->buildRow($intern, $records, $effectiveWorkingDays, $completedEffectiveDays, $todayString);
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'effective_working_days' => count($effectiveDates),
            'rows' => $rows,
        ];
    }

    /**
     * Restrict a list of effective working dates to those that fall within an
     * intern's internship period (inclusive). Interns without a start or end
     * date are not bounded on that side.
     *
     * @param  array<int, string>  $effectiveDates
     * @return array<int, string>
     */
    private function datesWithinInternship(array $effectiveDates, Intern $intern): array
    {
        $internStart = $intern->start_date?->toDateString();
        $internEnd = $intern->end_date?->toDateString();

        return array_values(array_filter(
            $effectiveDates,
            function (string $date) use ($internStart, $internEnd): bool {
                if ($internStart !== null && $date < $internStart) {
                    return false;
                }

                if ($internEnd !== null && $date > $internEnd) {
                    return false;
                }

                return true;
            }
        ));
    }

    /**
     * The list of effective working day dates (Y-m-d) within the period.
     *
     * Excludes weekends and every registered non-working day.
     *
     * @return array<int, string>
     */
    private function effectiveWorkingDates(Carbon $start, Carbon $end): array
    {
        $nonWorkingDays = NonWorkingDay::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->all();

        $nonWorkingLookup = array_flip($nonWorkingDays);

        $dates = [];

        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            if (Attendance::isWeekend($date)) {
                continue;
            }

            $key = $date->toDateString();

            if (isset($nonWorkingLookup[$key])) {
                continue;
            }

            $dates[] = $key;
        }

        return $dates;
    }

    /**
     * Fetch the interns to include in the recap, optionally filtered by program.
     *
     * @return Collection<int, Intern>
     */
    private function interns(?int $programId): Collection
    {
        return Intern::query()
            ->with(['user', 'internProgram'])
            ->when($programId, fn ($query) => $query->where('intern_program_id', $programId))
            ->whereHas('user')
            ->get()
            ->sortBy(fn (Intern $intern): string => (string) $intern->user?->name)
            ->values();
    }

    /**
     * Fetch attendance records for the given users within the period, grouped
     * by user id. A single query keeps this database-agnostic and efficient.
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, Collection<int, Attendance>>
     */
    private function attendancesByUser(array $userIds, Carbon $start, Carbon $end): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Compute the recap metrics for a single intern.
     *
     * @param  Collection<int, Attendance>  $records          Effective-day records only.
     * @param  int                          $completedEWD     Effective working days strictly before today.
     * @return array<string, mixed>
     */
    private function buildRow(
        Intern $intern,
        Collection $records,
        int $effectiveWorkingDays,
        int $completedEWD,
        string $todayString
    ): array {
        // Running totals (include today's record if it already exists). The
        // total working attendance (present|late) is split by work mode so the
        // recap can show WFO, WFH and Dinas separately.
        $working = $records->whereIn('status', self::WORKING_STATUSES);

        $hadir = $working->count();
        $wfo = $working->where('work_mode', Attendance::WORK_MODE_WFO)->count();
        $wfh = $working->where('work_mode', Attendance::WORK_MODE_WFH)->count();
        $dinas = $working->where('work_mode', Attendance::WORK_MODE_DINAS)->count();

        $late = $records->where('status', 'late')->count();
        $izin = $records->whereIn('status', self::LEAVE_STATUSES)->count();

        $tidakCo = $records->filter(fn (Attendance $attendance): bool =>
            $attendance->check_in_time !== null && $attendance->check_out_time === null
        )->count();

        // Tidak Absen is derived from completed days only. Today is still in
        // progress so it must not be counted as absent. We measure working
        // attendance and Izin on the same completed-day window to keep the
        // subtraction consistent.
        $completedRecords = $records->filter(
            fn (Attendance $attendance): bool => $attendance->attendance_date->toDateString() < $todayString
        );

        $hadirCompleted = $completedRecords->whereIn('status', self::WORKING_STATUSES)->count();
        $izinCompleted = $completedRecords->whereIn('status', self::LEAVE_STATUSES)->count();
        $tidakAbsen = $completedEWD - ($hadirCompleted + $izinCompleted);

        return [
            'intern' => $intern,
            'effective_working_days' => $effectiveWorkingDays,
            'hadir' => $hadir,
            'wfo' => $wfo,
            'wfh' => $wfh,
            'dinas' => $dinas,
            'izin' => $izin,
            'tidak_absen' => $tidakAbsen,
            'terlambat' => $late,
            'tidak_co' => $tidakCo,
            // Late is a subset of working attendance, so punctuality is measured
            // against total working attendance (WFO + WFH + Dinas).
            'persen_terlambat' => $this->percentage($late, $hadir),
            // Absence is measured against completed obligations only.
            'persen_tidak_absen' => $this->percentage($tidakAbsen, $completedEWD),
        ];
    }

    /**
     * Safe percentage helper that avoids division by zero.
     */
    private function percentage(int $value, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round($value / $total * 100, 1);
    }
}
