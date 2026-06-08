<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
