<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'request_number',
    'type',
    'reason',
    'start_date',
    'end_date',
    'total_days',
    'contact_phone',
    'address',
    'evidence_path',
    'status',
    'admin_note',
    'approved_by',
    'approved_at',
])]
class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use HasFactory;

    /**
     * The accessors to append to the model's array / JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'evidence_url',
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
            'total_days' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Boot the model and auto-generate the request number on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (LeaveRequest $leaveRequest): void {
            if (empty($leaveRequest->request_number)) {
                $leaveRequest->request_number = static::generateRequestNumber();
            }
        });
    }

    /**
     * Calculate the number of working days (HK) within a date range.
     *
     * Only valid working days are counted: weekends (Saturday and Sunday) and
     * any registered non-working day (national holiday, collective leave or
     * company holiday) are skipped. The working-day configuration is reused so
     * the result stays consistent with attendance handling.
     */
    public static function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();

        if ($end->lessThan($start)) {
            return 0;
        }

        // Pre-load registered non-working days within the range so the loop
        // does not query the database once per day.
        $holidays = NonWorkingDay::whereBetween('date', [
            $start->toDateString(),
            $end->toDateString(),
        ])
            ->pluck('date')
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->flip();

        $workingDays = 0;

        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            if (Attendance::isWeekend($date)) {
                continue;
            }

            if (! WorkingHour::isWorkingDay($date)) {
                continue;
            }

            if ($holidays->has($date->toDateString())) {
                continue;
            }

            $workingDays++;
        }

        return $workingDays;
    }

    /**
     * Generate a unique, human-readable request number (e.g. LR-20260608-0001).
     */
    public static function generateRequestNumber(): string
    {
        $prefix = 'LR-'.now()->format('Ymd').'-';
        $sequence = static::whereDate('created_at', today())->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Public URL to the uploaded evidence, or null when none is attached.
     */
    public function getEvidenceUrlAttribute(): ?string
    {
        if (empty($this->evidence_path)) {
            return null;
        }

        // asset('storage/...') resolves to APP_URL/storage/{path}, matching
        // the "public" disk URL — without hitting the untyped Filesystem contract.
        return asset('storage/'.$this->evidence_path);
    }

    /**
     * The intern who submitted the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who approved or rejected the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to approved leave requests for a user that cover a given date.
     *
     * @param  Builder<LeaveRequest>  $query
     * @param  \DateTimeInterface|string  $date
     * @return Builder<LeaveRequest>
     */
    public function scopeApprovedCovering(Builder $query, int $userId, $date): Builder
    {
        $date = Carbon::parse($date)->toDateString();

        return $query->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    /**
     * Indonesian label for the request type.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => ucfirst($this->type),
        };
    }

    /**
     * Indonesian label for the request status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Tailwind badge classes for the request status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    }
}
