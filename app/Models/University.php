<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    /** @use HasFactory<\Database\Factories\UniversityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lldikti',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only active universities.
     *
     * @param  Builder<University>  $query
     * @return Builder<University>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the study programs offered by this university.
     *
     * @return HasMany<StudyProgram, $this>
     */
    public function studyPrograms(): HasMany
    {
        return $this->hasMany(StudyProgram::class);
    }

    /**
     * Get the interns enrolled from this university.
     *
     * @return HasMany<Intern, $this>
     */
    public function interns(): HasMany
    {
        return $this->hasMany(Intern::class);
    }

    /**
     * Scope a query to universities whose name contains the given term.
     *
     * Uses a database-agnostic LIKE filter (no raw SQL) so it works on SQL
     * Server, MySQL and SQLite alike.
     *
     * @param  Builder<University>  $query
     * @return Builder<University>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where('name', 'like', '%'.$term.'%');
    }
}
