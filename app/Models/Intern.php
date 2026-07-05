<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Intern extends Model
{
    /** @use HasFactory<\Database\Factories\InternFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Internship participation statuses.
     *
     * Only INACTIVE is set manually by an administrator as an override that
     * disables the intern regardless of the calendar. The remaining statuses
     * are derived automatically from the internship dates (see effectiveStatus)
     * at read time, so the stored column never needs to be maintained by hand.
     */
    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'intern_program_id',
        'university_id',
        'study_program_id',
        'division_id',
        'user_id',
        'nim',
        'phone',
        'university',
        'major',
        'division',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * The accessors to append to the model's array / JSON form.
     *
     * Exposing the date-derived status means the UI always reflects the
     * calendar (and the server clock) instead of the stored column, which only
     * gets realigned by the daily schedule and can therefore lag behind on the
     * day a period starts or ends.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'effective_status',
    ];

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
     * The date-derived status surfaced to the UI.
     *
     * Always computed from the dates against the server clock so it stays
     * accurate even when the stored column has not yet been realigned by the
     * daily schedule.
     */
    public function getEffectiveStatusAttribute(): string
    {
        return $this->effectiveStatus();
    }

    /**
     * Scope a query to interns that are currently active on the given date.
     *
     * Active means not manually deactivated and the date falls within the
     * internship period. Expressed with the query builder only (no raw SQL) so
     * it stays portable across SQL Server, MySQL and SQLite.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Intern>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Intern>
     */
    public function scopeActiveOn(\Illuminate\Database\Eloquent\Builder $query, ?Carbon $date = null): \Illuminate\Database\Eloquent\Builder
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();

        return $query
            ->where('status', '!=', self::STATUS_INACTIVE)
            ->where(function ($q) use ($date): void {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $date);
            })
            ->where(function ($q) use ($date): void {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            });
    }

    /**
     * Scope a query to interns whose period has ended on or before the given
     * date and that are not manually deactivated (i.e. completed).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Intern>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Intern>
     */
    public function scopeCompletedOn(\Illuminate\Database\Eloquent\Builder $query, ?Carbon $date = null): \Illuminate\Database\Eloquent\Builder
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();

        return $query
            ->where('status', '!=', self::STATUS_INACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $date);
    }

    /**
     * Scope a query to interns whose period has not started yet on the given
     * date and that are not manually deactivated (i.e. upcoming).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Intern>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Intern>
     */
    public function scopeUpcomingOn(\Illuminate\Database\Eloquent\Builder $query, ?Carbon $date = null): \Illuminate\Database\Eloquent\Builder
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();

        return $query
            ->where('status', '!=', self::STATUS_INACTIVE)
            ->whereNotNull('start_date')
            ->whereDate('start_date', '>', $date);
    }

    /**
     * Scope a query to interns that are manually deactivated.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Intern>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Intern>
     */
    public function scopeInactive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope a query to interns that may access the intern portal on the given
     * date: not manually deactivated and whose period has not ended. This is
     * the query-level mirror of {@see canAccessPortal()} and therefore includes
     * both upcoming and active interns while excluding completed/ended and
     * deactivated ones. Uses the query builder only (no raw SQL) so it stays
     * portable across SQL Server, MySQL and SQLite.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Intern>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Intern>
     */
    public function scopeCanAccessPortalOn(\Illuminate\Database\Eloquent\Builder $query, ?Carbon $date = null): \Illuminate\Database\Eloquent\Builder
    {
        $date = ($date ?? Carbon::today())->copy()->startOfDay();

        return $query
            ->where('status', '!=', self::STATUS_INACTIVE)
            ->where(function ($q) use ($date): void {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            });
    }

    /**
     * Whether an administrator has manually deactivated this intern.
     *
     * This is the single manual override; everything else is date-driven.
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * The effective status of the internship for the given date.
     *
     * A manual deactivation always wins. Otherwise the status is derived from
     * the start and end dates so administrators never have to maintain it by
     * hand. Because it is computed from the dates against the server clock, the
     * value cannot be manipulated from the client.
     */
    public function effectiveStatus(?Carbon $date = null): string
    {
        if ($this->isInactive()) {
            return self::STATUS_INACTIVE;
        }

        if (! $this->hasStarted($date)) {
            return self::STATUS_UPCOMING;
        }

        if ($this->hasEnded($date)) {
            return self::STATUS_COMPLETED;
        }

        return self::STATUS_ACTIVE;
    }

    /**
     * Whether the intern may be archived (soft deleted).
     *
     * Only finished (Selesai) or manually deactivated (Non Aktif) interns can
     * be archived; upcoming (Akan Datang) and active (Aktif) participants must
     * stay in the active list.
     */
    public function canBeArchived(?Carbon $date = null): bool
    {
        return in_array(
            $this->effectiveStatus($date),
            [self::STATUS_COMPLETED, self::STATUS_INACTIVE],
            true
        );
    }

    /**
     * Whether the internship is currently running (within the period and not
     * manually deactivated).
     */
    public function isActive(?Carbon $date = null): bool
    {
        return $this->effectiveStatus($date) === self::STATUS_ACTIVE;
    }

    /**
     * Whether the internship has not started yet.
     */
    public function isUpcoming(?Carbon $date = null): bool
    {
        return $this->effectiveStatus($date) === self::STATUS_UPCOMING;
    }

    /**
     * Whether the internship has been completed (its end date has passed).
     */
    public function isCompleted(?Carbon $date = null): bool
    {
        return $this->effectiveStatus($date) === self::STATUS_COMPLETED;
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
     * Access is denied once the account is deactivated or once the internship
     * period has ended. Interns whose period has not started yet may still sign
     * in (e.g. to view their schedule) but cannot record attendance until the
     * start date.
     */
    public function canAccessPortal(?Carbon $date = null): bool
    {
        return ! $this->isInactive() && ! $this->hasEnded($date);
    }

    /**
     * Whether the intern may record attendance (check-in / check-out) on a date.
     */
    public function canRecordAttendanceOn(?Carbon $date = null): bool
    {
        return ! $this->isInactive() && $this->isWithinPeriod($date);
    }

    /**
     * Whether the intern may submit a leave request now.
     */
    public function canSubmitLeave(?Carbon $date = null): bool
    {
        return ! $this->isInactive() && ! $this->hasEnded($date);
    }

    /**
     * A human-readable (Indonesian) reason why attendance is blocked for the
     * given date, or null when attendance is allowed.
     */
    public function attendanceBlockReason(?Carbon $date = null): ?string
    {
        $date ??= Carbon::today();

        if ($this->isInactive()) {
            return 'Akun magang Anda telah dinonaktifkan, sehingga tidak dapat melakukan absensi. Silakan hubungi administrator.';
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
     * Get the university master-data record for this intern.
     *
     * Named with a "Ref" suffix to avoid colliding with the legacy free-text
     * `university` column that is retained on the model.
     *
     * @return BelongsTo<University, $this>
     */
    public function universityRef(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    /**
     * Get the study program master-data record for this intern.
     *
     * @return BelongsTo<StudyProgram, $this>
     */
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
    }

    /**
     * Get the division master-data record for this intern.
     *
     * Named with a "Ref" suffix to avoid colliding with the legacy free-text
     * `division` column that is retained on the model.
     *
     * @return BelongsTo<Division, $this>
     */
    public function divisionRef(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
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
