<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class WorkingHour extends Model
{
    /** @use HasFactory<\Database\Factories\WorkingHourFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'is_working_day',
    ];

    /**
     * In-request cache of every configured day keyed by ISO day-of-week.
     *
     * @var array<int, WorkingHour>|null
     */
    private static ?array $byDayCache = null;

    /**
     * Keep the per-request cache consistent after any change.
     */
    protected static function booted(): void
    {
        static::saved(static fn () => self::flushCache());
        static::deleted(static fn () => self::flushCache());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_working_day' => 'boolean',
        ];
    }

    /**
     * Indonesian labels for each ISO day-of-week (1 = Monday ... 7 = Sunday).
     *
     * @return array<int, string>
     */
    public static function dayLabels(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    /**
     * Indonesian label for this row's day-of-week.
     */
    public function dayLabel(): string
    {
        return static::dayLabels()[$this->day_of_week] ?? (string) $this->day_of_week;
    }

    /**
     * The default working-hour configuration keyed by ISO day-of-week.
     *
     * Monday-Thursday 08:00-17:00, Friday 08:00-15:00, weekend non-working.
     *
     * @return array<int, array{start_time: ?string, end_time: ?string, is_working_day: bool}>
     */
    public static function defaults(): array
    {
        return [
            1 => ['start_time' => '08:00', 'end_time' => '17:00', 'is_working_day' => true],
            2 => ['start_time' => '08:00', 'end_time' => '17:00', 'is_working_day' => true],
            3 => ['start_time' => '08:00', 'end_time' => '17:00', 'is_working_day' => true],
            4 => ['start_time' => '08:00', 'end_time' => '17:00', 'is_working_day' => true],
            5 => ['start_time' => '08:00', 'end_time' => '15:00', 'is_working_day' => true],
            6 => ['start_time' => null, 'end_time' => null, 'is_working_day' => false],
            7 => ['start_time' => null, 'end_time' => null, 'is_working_day' => false],
        ];
    }

    /**
     * Clear the in-request cache.
     */
    public static function flushCache(): void
    {
        self::$byDayCache = null;
    }

    /**
     * All configured rows keyed by ISO day-of-week (cached per request).
     *
     * @return array<int, WorkingHour>
     */
    public static function allByDay(): array
    {
        if (self::$byDayCache === null) {
            self::$byDayCache = static::query()->get()->keyBy('day_of_week')->all();
        }

        return self::$byDayCache;
    }

    /**
     * The configured row for the given date, or null when none exists.
     *
     * @param  \DateTimeInterface|string  $date
     */
    public static function forDate($date): ?self
    {
        $iso = Carbon::parse($date)->dayOfWeekIso;

        return self::allByDay()[$iso] ?? null;
    }

    /**
     * Ensure a row exists for every day of the week, filling any gaps with the
     * documented defaults. Idempotent; safe to call before listing or editing.
     */
    public static function ensureSeeded(): void
    {
        $existing = array_flip(static::query()->pluck('day_of_week')->all());

        foreach (self::defaults() as $day => $config) {
            if (! isset($existing[$day])) {
                static::query()->create(array_merge(['day_of_week' => $day], $config));
            }
        }

        self::flushCache();
    }

    /**
     * The effective configuration for a date: a stored row overrides the
     * documented defaults so attendance logic always has a value to work with.
     *
     * @param  \DateTimeInterface|string  $date
     * @return array{start_time: ?string, end_time: ?string, is_working_day: bool}
     */
    private static function configFor($date): array
    {
        $row = self::forDate($date);

        if ($row) {
            return [
                'start_time' => $row->start_time,
                'end_time' => $row->end_time,
                'is_working_day' => (bool) $row->is_working_day,
            ];
        }

        $iso = Carbon::parse($date)->dayOfWeekIso;

        return self::defaults()[$iso];
    }

    /**
     * Whether the given date is configured as a working day.
     *
     * @param  \DateTimeInterface|string  $date
     */
    public static function isWorkingDay($date): bool
    {
        return self::configFor($date)['is_working_day'];
    }

    /**
     * The configured start time (H:i) for the given date, or null when it is a
     * non-working day.
     *
     * @param  \DateTimeInterface|string  $date
     */
    public static function startTimeFor($date): ?string
    {
        $config = self::configFor($date);

        return $config['is_working_day'] ? $config['start_time'] : null;
    }

    /**
     * The configured end time (H:i) for the given date, or null when it is a
     * non-working day.
     *
     * @param  \DateTimeInterface|string  $date
     */
    public static function endTimeFor($date): ?string
    {
        $config = self::configFor($date);

        return $config['is_working_day'] ? $config['end_time'] : null;
    }
}
