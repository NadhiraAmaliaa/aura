<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'avatar_path', 'nik', 'password', 'role', 'is_active', 'division_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'division_id' => 'integer',
        ];
    }

    /**
     * The host-relative URL of the user's profile photo, or null when none is
     * set.
     *
     * Returns a path such as `/storage/avatars/xyz.jpg` rather than an
     * `APP_URL`-based absolute URL, so the mobile client can resolve it against
     * its own configured API host. This keeps avatars working across dev
     * machines/LAN IPs and in production without changing `APP_URL`.
     */
    public function avatarUrl(): ?string
    {
        if ($this->avatar_path === null) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return parse_url($disk->url($this->avatar_path), PHP_URL_PATH) ?: null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    /**
     * Whether the user is an internal staff account (admin or supervisor)
     * managed through the User Management module.
     */
    public function isInternalStaff(): bool
    {
        return $this->role === 'admin' || $this->role === 'supervisor';
    }

    public function isIntern(): bool
    {
        return $this->role === 'intern';
    }

    /**
     * Get the dashboard route name based on the user's role.
     *
     * Supervisors reuse the administrator dashboard and pages; their view is
     * scoped to their division by the controllers.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin', 'supervisor' => 'admin.dashboard',
            default => 'intern.dashboard',
        };
    }

    /**
     * Get the division a supervisor is assigned to manage.
     *
     * @return BelongsTo<Division, $this>
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the intern profile associated with the user.
     *
     * @return HasOne<Intern, $this>
     */
    public function intern(): HasOne
    {
        return $this->hasOne(Intern::class);
    }

    /**
     * Get the attendance records for the user.
     *
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leave requests submitted by the user.
     *
     * @return HasMany<LeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
