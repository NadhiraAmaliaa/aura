<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\WorkingHour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the daily attendance report for a single date.
 *
 * The report is presentation-oriented: for the selected date it produces one
 * row per active intern (optionally filtered by program and division), a set of
 * summary totals, and a distribution suitable for charting. Records are grouped
 * into reporting categories (WFO / WFH / Dinas / Izin / Tidak Hadir) rather than
 * raw attendance statuses.
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
     * Build the report for the given date.
     *
     * @return array<string, mixed>
     */
    public function build(Carbon $date, ?int $programId = null, ?int $divisionId = null): array
    {
        $day = $date->copy()->startOfDay();
        $isWorkingDay = ! Attendance::isNonWorkingDay($day);

        $interns = $this->interns($day, $programId, $divisionId);
        $userIds = $interns->pluck('user_id')->all();

        $attendances = $this->attendancesByUser($userIds, $day);
        $leaves = $this->approvedLeavesByUser($userIds, $day);

        $checkInSchedule = WorkingHour::startTimeFor($day);
        $checkOutSchedule = WorkingHour::endTimeFor($day);
        $dayLabel = WorkingHour::dayLabels()[$day->dayOfWeekIso] ?? '';

        $rows = [];

        foreach ($interns as $intern) {
            $rows[] = $this->buildRow(
                $intern,
                $attendances->get($intern->user_id),
                $leaves->get($intern->user_id),
                $day->toDateString(),
                $dayLabel,
                $isWorkingDay,
                $checkInSchedule,
                $checkOutSchedule
            );
        }

        return [
            'date' => $day->toDateString(),
            'day_label' => $dayLabel,
            'is_working_day' => $isWorkingDay,
            'summary' => $this->summarize($rows, $isWorkingDay),
            'chart' => $this->chart($rows, $isWorkingDay),
            'rows' => $rows,
        ];
    }

    /**
     * Fetch the interns active on the date, optionally filtered by program and
     * division, ordered by name.
     *
     * @return Collection<int, Intern>
     */
    private function interns(Carbon $day, ?int $programId, ?int $divisionId): Collection
    {
        return Intern::query()
            ->activeOn($day)
            ->with(['user', 'internProgram', 'divisionRef'])
            ->whereHas('user')
            ->when($programId, fn ($query) => $query->where('intern_program_id', $programId))
            ->when($divisionId, fn ($query) => $query->where('division_id', $divisionId))
            ->get()
            ->sortBy(fn (Intern $intern): string => (string) $intern->user?->name)
            ->values();
    }

    /**
     * Fetch the single attendance record per user for the date, keyed by user.
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, Attendance>
     */
    private function attendancesByUser(array $userIds, Carbon $day): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereDate('attendance_date', $day->toDateString())
            ->get()
            ->keyBy('user_id');
    }

    /**
     * Fetch the approved leave covering the date per user, keyed by user.
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, LeaveRequest>
     */
    private function approvedLeavesByUser(array $userIds, Carbon $day): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return LeaveRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $day->toDateString())
            ->whereDate('end_date', '>=', $day->toDateString())
            ->get()
            ->keyBy('user_id');
    }

    /**
     * Build a single report row for an intern on the date.
     *
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
        ?string $checkOutSchedule
    ): array {
        [$category, $jenisAbsen, $isLate] = $this->resolveCategory($attendance, $leave);

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
            'check_in' => $attendance?->check_in_time?->format('H:i'),
            'check_in_lat' => $attendance?->check_in_latitude,
            'check_in_long' => $attendance?->check_in_longitude,
            'check_out_schedule' => $checkOutSchedule,
            'check_out' => $attendance?->check_out_time?->format('H:i'),
            'check_out_lat' => $attendance?->check_out_latitude,
            'check_out_long' => $attendance?->check_out_longitude,
            // Placeholders for a future mood check-in/out feature.
            'mood_in' => null,
            'mood_out' => null,
        ];
    }

    /**
     * Resolve the reporting category, display label and late flag for a row.
     *
     * Present/late attendance is reported by its work mode (WFO/WFH/Dinas).
     * Permission and sick are reported as Izin/Sakit. When there is no record,
     * an approved leave still counts as Izin/Sakit; otherwise it is Tidak Absen.
     *
     * @return array{0: string, 1: string, 2: bool}
     */
    private function resolveCategory(?Attendance $attendance, ?LeaveRequest $leave): array
    {
        if ($attendance !== null) {
            return match ($attendance->status) {
                'present', 'late' => [
                    $attendance->work_mode ?: self::CATEGORY_WFO,
                    Attendance::workModeLabels()[$attendance->work_mode] ?? 'Hadir',
                    $attendance->status === 'late',
                ],
                'permission' => [self::CATEGORY_IZIN, 'Izin', false],
                'sick' => [self::CATEGORY_SAKIT, 'Sakit', false],
                'absent' => [self::CATEGORY_ALPHA, 'Alpha', false],
                default => [self::CATEGORY_TIDAK_ABSEN, 'TIDAK ABSEN', false],
            };
        }

        if ($leave !== null) {
            return $leave->type === 'sakit'
                ? [self::CATEGORY_SAKIT, 'Sakit', false]
                : [self::CATEGORY_IZIN, 'Izin', false];
        }

        return [self::CATEGORY_TIDAK_ABSEN, 'TIDAK ABSEN', false];
    }

    /**
     * Compute the summary card totals from the rows.
     *
     * Tidak Hadir only counts on working days; on non-working days interns are
     * not required to attend so an absence is not a shortfall.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function summarize(array $rows, bool $isWorkingDay): array
    {
        $working = [self::CATEGORY_WFO, self::CATEGORY_WFH, self::CATEGORY_DINAS];
        $izin = [self::CATEGORY_IZIN, self::CATEGORY_SAKIT];
        $absent = [self::CATEGORY_TIDAK_ABSEN, self::CATEGORY_ALPHA];

        return [
            'total_peserta' => count($rows),
            'total_hadir' => $this->countCategories($rows, $working),
            'terlambat' => count(array_filter($rows, fn (array $row): bool => $row['is_late'] === true)),
            'izin' => $this->countCategories($rows, $izin),
            'tidak_hadir' => $isWorkingDay ? $this->countCategories($rows, $absent) : 0,
        ];
    }

    /**
     * Compute the attendance distribution for the chart.
     *
     * Tidak Hadir is omitted on non-working days. The frontend treats an
     * all-zero distribution as an empty state.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function chart(array $rows, bool $isWorkingDay): array
    {
        return [
            'wfo' => $this->countCategories($rows, [self::CATEGORY_WFO]),
            'wfh' => $this->countCategories($rows, [self::CATEGORY_WFH]),
            'dinas' => $this->countCategories($rows, [self::CATEGORY_DINAS]),
            'izin' => $this->countCategories($rows, [self::CATEGORY_IZIN, self::CATEGORY_SAKIT]),
            'tidak_hadir' => $isWorkingDay
                ? $this->countCategories($rows, [self::CATEGORY_TIDAK_ABSEN, self::CATEGORY_ALPHA])
                : 0,
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
