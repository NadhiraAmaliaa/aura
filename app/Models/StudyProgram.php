<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    /** @use HasFactory<\Database\Factories\StudyProgramFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'university_id',
        'name',
        'level',
    ];

    /**
     * Get the university that offers this study program.
     *
     * @return BelongsTo<University, $this>
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Get the interns enrolled in this study program.
     *
     * @return HasMany<Intern, $this>
     */
    public function interns(): HasMany
    {
        return $this->hasMany(Intern::class);
    }

    /**
     * Scope a query to study programs whose name contains the given term.
     *
     * @param  Builder<StudyProgram>  $query
     * @return Builder<StudyProgram>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where('name', 'like', '%'.$term.'%');
    }

    /**
     * Scope a query to the study programs belonging to a university.
     *
     * @param  Builder<StudyProgram>  $query
     * @return Builder<StudyProgram>
     */
    public function scopeForUniversity(Builder $query, ?int $universityId): Builder
    {
        if ($universityId === null) {
            return $query;
        }

        return $query->where('university_id', $universityId);
    }
}
