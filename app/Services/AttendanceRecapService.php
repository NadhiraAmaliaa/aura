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
     * Attendance statuses that count as physically working (WFO).
     *
     * @var array<int, string>
     */
    private const WFO_STATUSES = ['present', 'late'];

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

        $effectiveDates = $this->effectiveWorkingDates($start, $end);
        $effectiveWorkingDays = count($effectiveDates);
        $effectiveLookup = array_flip($effectiveDates);

        // Only fully-elapsed effective working days count toward Tidak Absen.
        // Today is still in progress and must not be treated as missed.
        $todayString = Carbon::today()->toDateString();
        $completedEffectiveDays = count(array_filter(
            $effectiveDates,
            fn (string $date): bool => $date < $todayString
        ));

        $interns = $this->interns($programId);
        $attendancesByUser = $this->attendancesByUser($interns->pluck('user_id')->all(), $start, $end);

        $rows = [];

        foreach ($interns as $intern) {
            $records = ($attendancesByUser->get($intern->user_id) ?? collect())
                ->filter(fn (Attendance $attendance): bool => isset(
                    $effectiveLookup[$attendance->attendance_date->toDateString()]
                ));

            $rows[] = $this->buildRow($intern, $records, $effectiveWorkingDays, $completedEffectiveDays, $todayString);
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'effective_working_days' => $effectiveWorkingDays,
            'rows' => $rows,
        ];
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
        // Running totals (include today's record if it already exists).
        $wfo = $records->whereIn('status', self::WFO_STATUSES)->count();
        $late = $records->where('status', 'late')->count();
        $izin = $records->whereIn('status', self::LEAVE_STATUSES)->count();

        $tidakCo = $records->filter(fn (Attendance $attendance): bool =>
            $attendance->check_in_time !== null && $attendance->check_out_time === null
        )->count();

        // Tidak Absen is derived from completed days only. Today is still in
        // progress so it must not be counted as absent. We measure WFO and Izin
        // on the same completed-day window to keep the subtraction consistent.
        $completedRecords = $records->filter(
            fn (Attendance $attendance): bool => $attendance->attendance_date->toDateString() < $todayString
        );

        $wfoCompleted = $completedRecords->whereIn('status', self::WFO_STATUSES)->count();
        $izinCompleted = $completedRecords->whereIn('status', self::LEAVE_STATUSES)->count();
        $tidakAbsen = $completedEWD - ($wfoCompleted + $izinCompleted);

        return [
            'intern' => $intern,
            'effective_working_days' => $effectiveWorkingDays,
            'wfo' => $wfo,
            'izin' => $izin,
            'tidak_absen' => $tidakAbsen,
            'terlambat' => $late,
            'tidak_co' => $tidakCo,
            // Late is a subset of WFO, so punctuality is measured against WFO.
            'persen_terlambat' => $this->percentage($late, $wfo),
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
