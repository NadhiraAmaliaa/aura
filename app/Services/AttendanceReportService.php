<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\WorkingHour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the attendance report for a date range.
 *
 * The report is presentation-oriented: for each date in the range it produces
 * one row per active intern (optionally filtered by program and division).
 * Records are grouped into reporting categories (WFO / WFH / Dinas / Izin /
 * Tidak Absen) rather than raw attendance statuses.
 *
 * The underlying attendance business logic (status, working days, schedules)
 * lives in the models; this service only reads and presents it.
 */
class AttendanceReportService
{
    /**
     * Reporting category keys used by the summary, chart and table.
     */
    public const CATEGORY_WFO = 'wfo';

    public const CATEGORY_WFH = 'wfh';

    public const CATEGORY_DINAS = 'dinas';

    public const CATEGORY_IZIN = 'izin';

    public const CATEGORY_SAKIT = 'sakit';

    public const CATEGORY_ALPHA = 'alpha';

    public const CATEGORY_TIDAK_ABSEN = 'tidak_absen';

    /**
     * Build the report for the given date range.
     *
     * @return array<string, mixed>
     */
    public function build(
        Carbon $startDate,
        Carbon $endDate,
        ?int $programId = null,
        ?int $divisionId = null
    ): array {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        // Pre-fetch data for the entire range to avoid N+1.
        $candidates = $this->candidateInterns($start, $end, $programId, $divisionId);
        $allUserIds = $candidates->pluck('user_id')->filter()->values()->all();

        $attendanceMap = $this->attendancesByUserAndDate($allUserIds, $start, $end);
        $leavesByUser = $this->approvedLeavesGroupedByUser($allUserIds, $start, $end);
        $locations = AttendanceLocation::active()->get();

        $allRows = [];

        foreach ($this->dateRange($start, $end) as $day) {
            $isWorkingDay = ! Attendance::isNonWorkingDay($day);
            $dayLabel = WorkingHour::dayLabels()[$day->dayOfWeekIso] ?? '';
            $checkInSchedule = WorkingHour::startTimeFor($day);
            $checkOutSchedule = WorkingHour::endTimeFor($day);

            foreach ($candidates as $intern) {
                if (! $this->isActiveOnDate($intern, $day)) {
                    continue;
                }

                $mapKey = $intern->user_id.'_'.$day->toDateString();
                $attendance = $attendanceMap->get($mapKey);
                $leave = $this->findLeaveForDate(
                    $leavesByUser->get($intern->user_id, collect()),
                    $day
                );

                $allRows[] = $this->buildRow(
                    $intern,
                    $attendance,
                    $leave,
                    $day->toDateString(),
                    $dayLabel,
                    $isWorkingDay,
                    $checkInSchedule,
                    $checkOutSchedule,
                    $locations
                );
            }
        }

        // Sort by date then name for consistent output.
        usort($allRows, fn (array $a, array $b): int =>
            $a['tanggal'] !== $b['tanggal']
                ? strcmp($a['tanggal'], $b['tanggal'])
                : strcmp((string) $a['nama'], (string) $b['nama'])
        );

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'summary' => $this->summarize($allRows),
            'chart' => $this->chart($allRows),
            'rows' => $allRows,
        ];
    }

    /**
     * Fetch interns that overlap the date range, optionally filtered.
     * Per-date filtering is done in PHP via isActiveOnDate().
     *
     * @return Collection<int, Intern>
     */
    private function candidateInterns(
        Carbon $start,
        Carbon $end,
        ?int $programId,
        ?int $divisionId
    ): Collection {
        return Intern::query()
            ->withTrashed()
            ->whereHas('user')
            ->with(['user', 'internProgram', 'divisionRef'])
            ->where('status', '!=', 'inactive')
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $end->toDateString()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $start->toDateString()))
            ->when($programId, fn ($q) => $q->where('intern_program_id', $programId))
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->get()
            ->sortBy(fn (Intern $intern): string => (string) $intern->user?->name)
            ->values();
    }

    /**
     * Whether an intern is active on a specific date (in-memory check).
     */
    private function isActiveOnDate(Intern $intern, Carbon $day): bool
    {
        if ($intern->status === 'inactive') {
            return false;
        }

        /** @var Carbon|null $startDate */
        $startDate = $intern->start_date;
        /** @var Carbon|null $endDate */
        $endDate = $intern->end_date;

        if ($startDate !== null && $day->lt($startDate)) {
            return false;
        }

        if ($endDate !== null && $day->gt($endDate)) {
            return false;
        }

        return true;
    }

    /**
     * Batch-fetch attendances for the range, keyed by "{user_id}_{date}".
     *
     * @param  array<int, int>  $userIds
     * @return Collection<string, Attendance>
     */
    private function attendancesByUserAndDate(
        array $userIds,
        Carbon $start,
        Carbon $end
    ): Collection {
        if ($userIds === []) {
            return collect();
        }

        return Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $a): string => $a->user_id.'_'.$a->attendance_date->toDateString());
    }

    /**
     * Batch-fetch approved leaves that overlap the range, grouped by user_id.
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, Collection<int, LeaveRequest>>
     */
    private function approvedLeavesGroupedByUser(
        array $userIds,
        Carbon $start,
        Carbon $end
    ): Collection {
        if ($userIds === []) {
            return collect();
        }

        return LeaveRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Find the first approved leave for a user that covers the given date.
     *
     * @param  Collection<int, LeaveRequest>  $leaves
     */
    private function findLeaveForDate(Collection $leaves, Carbon $day): ?LeaveRequest
    {
        return $leaves->first(
            fn (LeaveRequest $l): bool => ! $day->lt($l->start_date) && ! $day->gt($l->end_date)
        );
    }

    /**
     * Produce all dates in the inclusive range as Carbon instances.
     *
     * @return array<int, Carbon>
     */
    private function dateRange(Carbon $start, Carbon $end): array
    {
        $dates = [];
        $current = $start->copy();

        while (! $current->gt($end)) {
            $dates[] = $current->copy();
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Build a single report row for an intern on a date.
     *
     * @param  Collection<int, AttendanceLocation>  $locations
     * @return array<string, mixed>
     */
    private function buildRow(
        Intern $intern,
        ?Attendance $attendance,
        ?LeaveRequest $leave,
        string $date,
        string $dayLabel,
        bool $isWorkingDay,
        ?string $checkInSchedule,
        ?string $checkOutSchedule,
        Collection $locations
    ): array {
        [$category, $jenisAbsen] = $this->resolveCategory($attendance, $leave);

        $checkInStr = $attendance?->check_in_time?->format('H:i');
        $checkOutStr = $attendance?->check_out_time?->format('H:i');

        // Lateness is derived from the actual check-in time vs the schedule,
        // not from the stored status, so it is always accurate.
        $lateSeconds = $this->lateArrivalSeconds($checkInStr, $checkInSchedule, $category);
        $isLate = ($lateSeconds ?? 0) > 0;

        return [
            'intern_id' => $intern->id,
            'attendance_id' => $attendance?->id,
            'nim' => $intern->nim,
            'nama' => $intern->user?->name,
            'tanggal' => $date,
            'program' => $intern->internProgram?->name,
            'divisi' => $intern->divisionRef?->name ?? $intern->division,
            'hari' => $dayLabel,
            'hari_kerja' => $isWorkingDay,
            'category' => $category,
            'jenis_absen' => $jenisAbsen,
            'is_late' => $isLate,
            'check_in_schedule' => $checkInSchedule,
            'check_in' => $checkInStr,
            'check_in_lat' => $attendance?->check_in_latitude,
            'check_in_long' => $attendance?->check_in_longitude,
            'check_out_schedule' => $checkOutSchedule,
            'check_out' => $checkOutStr,
            'check_out_lat' => $attendance?->check_out_latitude,
            'check_out_long' => $attendance?->check_out_longitude,
            // Computed columns.
            'jam_bekerja' => $this->computeJamBekerja($checkInStr, $checkOutStr),
            'jarak' => $this->computeJarak($attendance, $locations),
            'status_kedatangan' => $this->computeStatusKedatangan($category, $isLate),
            'status_kepulangan' => $this->computeStatusKepulangan($category, $attendance),
            'keterlambatan' => $this->formatKeterlambatan($lateSeconds),
            // Placeholders for a future mood check-in/out feature.
            'mood_in' => null,
            'mood_out' => null,
        ];
    }

    /**
     * Resolve the reporting category and display label for a row.
     *
     * @return array{0: string, 1: string}
     */
    private function resolveCategory(?Attendance $attendance, ?LeaveRequest $leave): array
    {
        if ($attendance !== null) {
            return match ($attendance->status) {
                'present', 'late' => [
                    $attendance->work_mode ?: self::CATEGORY_WFO,
                    Attendance::workModeLabels()[$attendance->work_mode] ?? 'Hadir',
                ],
                'permission' => [self::CATEGORY_IZIN, 'Izin'],
                'sick' => [self::CATEGORY_SAKIT, 'Sakit'],
                'absent' => [self::CATEGORY_ALPHA, 'Alpha'],
                default => [self::CATEGORY_TIDAK_ABSEN, 'Tidak Absen'],
            };
        }

        if ($leave !== null) {
            return $leave->type === 'sakit'
                ? [self::CATEGORY_SAKIT, 'Sakit']
                : [self::CATEGORY_IZIN, 'Izin'];
        }

        return [self::CATEGORY_TIDAK_ABSEN, 'Tidak Absen'];
    }

    /**
     * Calculate working duration (HH:MM:SS) between check-in and check-out.
     * Returns null when check-out is missing.
     */
    private function computeJamBekerja(?string $checkIn, ?string $checkOut): ?string
    {
        if ($checkIn === null || $checkOut === null) {
            return null;
        }

        $in = Carbon::createFromFormat('H:i', $checkIn);
        $out = Carbon::createFromFormat('H:i', $checkOut);

        if ($out->lessThan($in)) {
            // Handle midnight crossover (unlikely but safe).
            $out->addDay();
        }

        $seconds = (int) abs($out->diffInSeconds($in));

        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    /**
     * Calculate the distance in metres from the check-in location to the
     * nearest active attendance location. Returns null when coordinates or
     * locations are unavailable.
     *
     * @param  Collection<int, AttendanceLocation>  $locations
     */
    private function computeJarak(?Attendance $attendance, Collection $locations): ?int
    {
        if ($attendance === null
            || $attendance->check_in_latitude === null
            || $attendance->check_in_longitude === null
            || $locations->isEmpty()
        ) {
            return null;
        }

        $lat = (float) $attendance->check_in_latitude;
        $lng = (float) $attendance->check_in_longitude;
        $minDist = PHP_INT_MAX;

        foreach ($locations as $loc) {
            $dist = $this->haversine($lat, $lng, (float) $loc->latitude, (float) $loc->longitude);
            if ($dist < $minDist) {
                $minDist = $dist;
            }
        }

        return $minDist < PHP_INT_MAX ? (int) round($minDist) : null;
    }

    /**
     * Resolve Status Kedatangan for a row.
     * "Terlambat Datang" when late; "Tidak Absen" when absent; empty otherwise.
     */
    private function computeStatusKedatangan(string $category, bool $isLate): string
    {
        if ($category === self::CATEGORY_TIDAK_ABSEN || $category === self::CATEGORY_ALPHA) {
            return 'Tidak Absen';
        }

        return $isLate ? 'Terlambat Datang' : '';
    }

    /**
     * Resolve Status Kepulangan for a row.
     * "Tidak Check Out" when checked in but no check-out; "Tidak Absen" when
     * absent; empty otherwise.
     */
    private function computeStatusKepulangan(string $category, ?Attendance $attendance): string
    {
        if ($category === self::CATEGORY_TIDAK_ABSEN || $category === self::CATEGORY_ALPHA) {
            return 'Tidak Absen';
        }

        if ($attendance !== null
            && $attendance->check_in_time !== null
            && $attendance->check_out_time === null
        ) {
            return 'Tidak Check Out';
        }

        return '';
    }

    /**
     * Seconds late on arrival = check-in minus schedule.
     *
     * Returns null when there is no arrival to evaluate (absent / leave rows),
     * 0 when the intern arrived on time or early, and the positive difference
     * (in seconds) when late.
     */
    private function lateArrivalSeconds(?string $checkIn, ?string $schedule, string $category): ?int
    {
        if ($checkIn === null
            || $schedule === null
            || $category === self::CATEGORY_TIDAK_ABSEN
            || $category === self::CATEGORY_ALPHA
            || $category === self::CATEGORY_IZIN
            || $category === self::CATEGORY_SAKIT
        ) {
            return null;
        }

        $scheduleCarbon = Carbon::createFromFormat('H:i', $schedule);
        $checkInCarbon = Carbon::createFromFormat('H:i', $checkIn);

        if ($checkInCarbon->lessThanOrEqualTo($scheduleCarbon)) {
            return 0;
        }

        return (int) abs($checkInCarbon->diffInSeconds($scheduleCarbon));
    }

    /**
     * Format the lateness for display.
     * "0" when on time or not applicable; "HH:MM:SS" difference when late.
     */
    private function formatKeterlambatan(?int $lateSeconds): string
    {
        if ($lateSeconds === null || $lateSeconds <= 0) {
            return '0';
        }

        return sprintf(
            '%02d:%02d:%02d',
            intdiv($lateSeconds, 3600),
            intdiv($lateSeconds % 3600, 60),
            $lateSeconds % 60
        );
    }

    /**
     * Haversine distance formula — returns distance in metres.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371000.0; // Earth radius in metres.
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlambda = deg2rad($lng2 - $lng1);
        $a = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlambda / 2) ** 2;

        return 2.0 * $R * asin(sqrt($a));
    }

    /**
     * Aggregate summary card totals across the full row set.
     * "Tidak Hadir" only counts rows from working days.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function summarize(array $rows): array
    {
        $working = [self::CATEGORY_WFO, self::CATEGORY_WFH, self::CATEGORY_DINAS];
        $izin = [self::CATEGORY_IZIN, self::CATEGORY_SAKIT];
        $absent = [self::CATEGORY_TIDAK_ABSEN, self::CATEGORY_ALPHA];

        $uniqueInterns = count(array_unique(array_column($rows, 'intern_id')));

        $tidakHadir = count(array_filter(
            $rows,
            fn (array $row): bool => $row['hari_kerja'] && in_array($row['category'], $absent, true)
        ));

        return [
            'total_peserta' => $uniqueInterns,
            'total_hadir' => $this->countCategories($rows, $working),
            'terlambat' => count(array_filter($rows, fn (array $row): bool => $row['is_late'] === true)),
            'izin' => $this->countCategories($rows, $izin),
            'tidak_hadir' => $tidakHadir,
        ];
    }

    /**
     * Aggregate chart distribution across the full row set.
     * "Tidak Hadir" only counts rows from working days.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function chart(array $rows): array
    {
        $absent = [self::CATEGORY_TIDAK_ABSEN, self::CATEGORY_ALPHA];

        return [
            'wfo' => $this->countCategories($rows, [self::CATEGORY_WFO]),
            'wfh' => $this->countCategories($rows, [self::CATEGORY_WFH]),
            'dinas' => $this->countCategories($rows, [self::CATEGORY_DINAS]),
            'izin' => $this->countCategories($rows, [self::CATEGORY_IZIN, self::CATEGORY_SAKIT]),
            'tidak_hadir' => count(array_filter(
                $rows,
                fn (array $row): bool => $row['hari_kerja'] && in_array($row['category'], $absent, true)
            )),
        ];
    }

    /**
     * Count rows whose category is in the given set.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $categories
     */
    private function countCategories(array $rows, array $categories): int
    {
        return count(array_filter(
            $rows,
            fn (array $row): bool => in_array($row['category'], $categories, true)
        ));
    }
}
