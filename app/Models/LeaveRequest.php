<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
