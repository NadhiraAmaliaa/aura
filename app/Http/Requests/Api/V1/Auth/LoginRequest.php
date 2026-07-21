<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Models\Intern;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Mobile intern login request.
 *
 * The mobile app is intern-only, so it mirrors the intern branch of the web
 * {@see \App\Http\Requests\Auth\LoginRequest}: an intern is identified by the
 * (university, NIM) pair plus a password. Uses the query builder and hasher
 * only (no raw SQL) so it stays portable across SQL Server, MySQL and SQLite.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'nim' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom attribute names for clearer validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'university_id' => 'universitas',
            'nim' => 'NIM',
            'password' => 'kata sandi',
        ];
    }

    /**
     * Attempt to authenticate the intern credentials and return the user.
     *
     * The intern is resolved by the (university, NIM) pair, then the linked
     * user account's password is verified. This performs authentication only;
     * authorization to use the portal (active internship period) is enforced
     * separately by the controller so it can return a distinct 403 response.
     *
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $intern = Intern::query()
            ->with('user')
            ->where('university_id', $this->integer('university_id'))
            ->where('nim', (string) $this->input('nim'))
            ->first();

        $user = $intern?->user;

        if ($user === null || $user->role !== 'intern') {
            // Run a dummy hash check to reduce user-enumeration timing leaks.
            Hash::check((string) $this->input('password'), '$2y$12$'.str_repeat('0', 53));

            $this->failAuthentication();
        }

        if (! Hash::check((string) $this->input('password'), $user->password)) {
            $this->failAuthentication();
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Record a failed attempt and throw the standard invalid-credentials error.
     *
     * @throws ValidationException
     */
    protected function failAuthentication(): never
    {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'nim' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'nim' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = $this->integer('university_id').':'.$this->input('nim');

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
