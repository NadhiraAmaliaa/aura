<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'intern_program_id',
    'user_id',
    'nim',
    'phone',
    'university',
    'major',
    'division',
    'start_date',
    'end_date',
    'status',
])]
class Intern extends Model
{
    /** @use HasFactory<\Database\Factories\InternFactory> */
    use HasFactory;

    /**
     * Internship participation statuses.
     */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Whether the intern's account is flagged active by an administrator.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Whether the intern has been deactivated.
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Whether the intern has been marked as completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Whether the internship has begun on or before the given date.
     *
     * An intern without a start date is treated as already started.
     */
    public function hasStarted(?Carbon $date = null): bool
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();

        return $this->start_date === null
            || $date->greaterThanOrEqualTo($this->start_date->copy()->startOfDay());
    }

    /**
     * Whether the internship period has ended before the given date.
     *
     * An intern without an end date is treated as never ending.
     */
    public function hasEnded(?Carbon $date = null): bool
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();

        return $this->end_date !== null
            && $date->greaterThan($this->end_date->copy()->startOfDay());
    }

    /**
     * Whether the given date falls within the internship period (inclusive).
     */
    public function isWithinPeriod(?Carbon $date = null): bool
    {
        return $this->hasStarted($date) && ! $this->hasEnded($date);
    }

    /**
     * Whether the intern may access the intern portal at all.
     *
     * Access is denied once the account is deactivated or completed, or once
     * the internship period has ended. Interns whose period has not started yet
     * may still sign in (e.g. to view their schedule) but cannot record
     * attendance until the start date.
     */
    public function canAccessPortal(?Carbon $date = null): bool
    {
        return $this->isActive() && ! $this->hasEnded($date);
    }

    /**
     * Whether the intern may record attendance (check-in / check-out) on a date.
     */
    public function canRecordAttendanceOn(?Carbon $date = null): bool
    {
        return $this->isActive() && $this->isWithinPeriod($date);
    }

    /**
     * Whether the intern may submit a leave request now.
     */
    public function canSubmitLeave(?Carbon $date = null): bool
    {
        return $this->isActive() && ! $this->hasEnded($date);
    }

    /**
     * A human-readable (Indonesian) reason why attendance is blocked for the
     * given date, or null when attendance is allowed.
     */
    public function attendanceBlockReason(?Carbon $date = null): ?string
    {
        $date ??= Carbon::today();

        if (! $this->isActive()) {
            return 'Akun magang Anda tidak berstatus aktif, sehingga tidak dapat melakukan absensi. Silakan hubungi administrator.';
        }

        if (! $this->hasStarted($date)) {
            $when = $this->start_date ? ' (mulai '.$this->start_date->format('d-m-Y').')' : '';

            return 'Masa magang Anda belum dimulai'.$when.', sehingga belum dapat melakukan absensi.';
        }

        if ($this->hasEnded($date)) {
            $when = $this->end_date ? ' (berakhir '.$this->end_date->format('d-m-Y').')' : '';

            return 'Masa magang Anda telah berakhir'.$when.', sehingga tidak dapat melakukan absensi.';
        }

        return null;
    }

    /**
     * Get the program this intern belongs to.
     *
     * @return BelongsTo<InternProgram, $this>
     */
    public function internProgram(): BelongsTo
    {
        return $this->belongsTo(InternProgram::class);
    }

    /**
     * Get the user account associated with this intern.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
