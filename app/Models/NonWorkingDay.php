<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable([
    'date',
    'name',
    'type',
])]
class NonWorkingDay extends Model
{
    /** @use HasFactory<\Database\Factories\NonWorkingDayFactory> */
    use HasFactory;

    /**
     * Supported non-working day types.
     */
    public const TYPE_NATIONAL_HOLIDAY = 'national_holiday';

    public const TYPE_COLLECTIVE_LEAVE = 'collective_leave';

    public const TYPE_COMPANY_HOLIDAY = 'company_holiday';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Scope the query to a single calendar date.
     *
     * @param  Builder<NonWorkingDay>  $query
     * @param  \DateTimeInterface|string  $date
     * @return Builder<NonWorkingDay>
     */
    public function scopeOnDate(Builder $query, $date): Builder
    {
        return $query->whereDate('date', Carbon::parse($date)->toDateString());
    }

    /**
     * Whether a registered non-working day exists for the given date.
     *
     * Reusable by attendance handling and future recap/reporting features.
     *
     * @param  \DateTimeInterface|string  $date
     */
    public static function existsOn($date): bool
    {
        return static::query()->onDate($date)->exists();
    }

    /**
     * Indonesian labels for each non-working day type.
     *
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_NATIONAL_HOLIDAY => 'Hari Libur Nasional',
            self::TYPE_COLLECTIVE_LEAVE => 'Cuti Bersama',
            self::TYPE_COMPANY_HOLIDAY => 'Libur Perusahaan',
        ];
    }

    /**
     * Indonesian label for this record's type.
     */
    public function typeLabel(): string
    {
        return static::typeLabels()[$this->type] ?? ucfirst((string) $this->type);
    }
}
