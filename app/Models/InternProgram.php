<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description'])]
class InternProgram extends Model
{
    /** @use HasFactory<\Database\Factories\InternProgramFactory> */
    use HasFactory;

    /**
     * Get the interns that belong to this program.
     *
     * @return HasMany<Intern, $this>
     */
    public function interns(): HasMany
    {
        return $this->hasMany(Intern::class);
    }
}
