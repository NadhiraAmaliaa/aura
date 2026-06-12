<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * A plain array (instead of the #[Fillable] attribute) is used to keep the
     * model compatible with PHP 8.2+ and earlier Laravel releases.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'work_mode',
        'notes',
    ];

    /**
     * The standard work start time. Check-ins after this time are marked late
     * (WFO and WFH).
     */
    public const WORK_START_TIME = '08:00';

    /**
     * Work modes describing how the attendance was performed.
     */
    public const WORK_MODE_WFO = 'wfo';

    public const WORK_MODE_WFH = 'wfh';

    public const WORK_MODE_DINAS = 'dinas';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime:H:i',
            'check_out_time' => 'datetime:H:i',
            'check_in_latitude' => 'decimal:7',
            'check_in_longitude' => 'decimal:7',
            'check_out_latitude' => 'decimal:7',
            'check_out_longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the user that owns the attendance record.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine the attendance status from a check-in moment.
     *
     * Rules by work mode:
     * - Dinas (official duty): may be performed anytime and anywhere, so it is
     *   never late and is always recorded as present.
     * - WFO / WFH: late rules apply. A check-in after the work start time is
     *   late; otherwise present.
     *
     * Non-working days (weekends, national holidays, collective leave and
     * company holidays) carry no late rules and are always present.
     */
    public static function determineStatus(
        Carbon $checkInTime,
        string $workMode = self::WORK_MODE_WFO
    ): string {
        if ($workMode === self::WORK_MODE_DINAS) {
            return 'present';
        }

        if (self::isNonWorkingDay($checkInTime)) {
            return 'present';
        }

        $start = $checkInTime->copy()->setTimeFromTimeString(self::WORK_START_TIME);

        return $checkInTime->greaterThan($start) ? 'late' : 'present';
    }

    /**
     * Whether the given date falls on a weekend (Saturday or Sunday).
     */
    public static function isWeekend(Carbon $date): bool
    {
        return $date->dayOfWeekIso >= 6;
    }

    /**
     * Whether the given date is a non-working day.
     *
     * A non-working day is a weekend or a date registered as a national
     * holiday, collective leave or company holiday. Such days carry no late
     * rules; valid attendance on them is treated as present.
     */
    public static function isNonWorkingDay(Carbon $date): bool
    {
        if (self::isWeekend($date)) {
            return true;
        }

        return NonWorkingDay::existsOn($date);
    }

    /**
     * Whether a check-in is allowed at the given moment for a work mode.
     *
     * - Dinas: always allowed (anytime, anywhere — no deadline).
     * - Non-working days: always allowed (special activities).
     * - WFO / WFH on a working day: allowed up to the working-day end time.
     */
    public static function isCheckInAllowed(
        Carbon $checkInTime,
        string $workMode = self::WORK_MODE_WFO
    ): bool {
        $deadline = self::checkInDeadline($checkInTime, $workMode);

        if ($deadline === null) {
            return true;
        }

        $cutoff = $checkInTime->copy()->setTimeFromTimeString($deadline);

        return $checkInTime->lessThanOrEqualTo($cutoff);
    }

    /**
     * The latest time (H:i) a check-in is accepted for a work mode, or null when
     * there is no cut-off (Dinas or non-working days).
     */
    public static function checkInDeadline(
        Carbon $date,
        string $workMode = self::WORK_MODE_WFO
    ): ?string {
        if ($workMode === self::WORK_MODE_DINAS) {
            return null;
        }

        if (self::isNonWorkingDay($date)) {
            return null;
        }

        // WFO and WFH: the working-day end time.
        return self::expectedCheckOutTime($date);
    }

    /**
     * Whether a work mode requires on-site location validation (geofencing).
     *
     * Only WFO is performed on company premises, so only WFO will be validated
     * against the office location and radius. The geofence enforcement itself
     * is intentionally not implemented yet and will be added later.
     */
    public static function requiresGeofence(string $workMode): bool
    {
        return $workMode === self::WORK_MODE_WFO;
    }

    /**
     * Map a leave request type to its corresponding attendance status.
     */
    public static function statusForLeaveType(string $leaveType): string
    {
        return match ($leaveType) {
            'sakit' => 'sick',
            'izin' => 'permission',
            default => 'permission',
        };
    }

    /**
     * The expected check-out time for a given date (information only).
     *
     * Monday-Thursday 17:00, Friday 15:00. Non-working days have no fixed time
     * because attendance is for overtime or special activities.
     */
    public static function expectedCheckOutTime(Carbon $date): ?string
    {
        if (self::isNonWorkingDay($date)) {
            return null;
        }

        return match ($date->dayOfWeekIso) {
            1, 2, 3, 4 => '17:00',
            5 => '15:00',
            default => null,
        };
    }

    /**
     * Indonesian labels for each attendance status.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'absent' => 'Alpha',
        ];
    }

    /**
     * Indonesian label for this record's status.
     */
    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Tailwind badge classes for this record's status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'present' => 'bg-green-100 text-green-800',
            'late' => 'bg-yellow-100 text-yellow-800',
            'sick' => 'bg-orange-100 text-orange-800',
            'permission' => 'bg-blue-100 text-blue-800',
            'absent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Indonesian labels for each work mode.
     *
     * @return array<string, string>
     */
    public static function workModeLabels(): array
    {
        return [
            self::WORK_MODE_WFO => 'WFO',
            self::WORK_MODE_WFH => 'WFH',
            self::WORK_MODE_DINAS => 'Dinas',
        ];
    }

    /**
     * Indonesian label for this record's work mode.
     */
    public function workModeLabel(): ?string
    {
        if ($this->work_mode === null) {
            return null;
        }

        return static::workModeLabels()[$this->work_mode] ?? ucfirst((string) $this->work_mode);
    }
}
