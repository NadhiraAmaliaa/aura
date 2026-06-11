<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'attendance_date',
    'check_in_time',
    'check_out_time',
    'check_in_latitude',
    'check_in_longitude',
    'check_out_latitude',
    'check_out_longitude',
    'status',
    'notes',
])]
class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    /**
     * The standard work start time. Check-ins after this time are marked late.
     */
    public const WORK_START_TIME = '08:00';

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
     * Weekends are always treated as present (overtime / special activities,
     * no late rules). On working days, present when checked in at or before
     * the work start time, otherwise late.
     */
    public static function determineStatus(Carbon $checkInTime): string
    {
        if (self::isWeekend($checkInTime)) {
            return 'present';
        }

        $threshold = $checkInTime->copy()->setTimeFromTimeString(self::WORK_START_TIME);

        return $checkInTime->greaterThan($threshold) ? 'late' : 'present';
    }

    /**
     * Whether the given date falls on a weekend (Saturday or Sunday).
     */
    public static function isWeekend(Carbon $date): bool
    {
        return $date->dayOfWeekIso >= 6;
    }

    /**
     * Whether a check-in is allowed at the given moment.
     *
     * Weekends are always allowed. On working days, a check-in is only
     * allowed up to the defined end of working hours for that day.
     */
    public static function isCheckInAllowed(Carbon $checkInTime): bool
    {
        if (self::isWeekend($checkInTime)) {
            return true;
        }

        $endTime = self::expectedCheckOutTime($checkInTime);

        if ($endTime === null) {
            return true;
        }

        $cutoff = $checkInTime->copy()->setTimeFromTimeString($endTime);

        return $checkInTime->lessThanOrEqualTo($cutoff);
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
     * Monday-Thursday 17:00, Friday 15:00. Weekends have no fixed time
     * because attendance is for overtime or special activities.
     */
    public static function expectedCheckOutTime(Carbon $date): ?string
    {
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
}
