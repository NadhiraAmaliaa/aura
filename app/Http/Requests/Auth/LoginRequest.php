<?php

namespace App\Http\Requests\Auth;

use App\Models\Intern;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
     * Two login modes are supported:
     *  - admin: NIK + password.
     *  - intern: university + NIM + password. A NIM is only unique within a
     *    single university, so the identity is the (university, NIM) pair.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isInternLogin()) {
            return [
                'login_as' => ['required', 'in:admin,intern'],
                'university_id' => ['required', 'integer', 'exists:universities,id'],
                'nim' => ['required', 'string', 'max:50'],
                'password' => ['required', 'string'],
            ];
        }

        return [
            'login_as' => ['nullable', 'in:admin,intern'],
            'nik' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
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
            'nik' => 'NIK',
            'password' => 'kata sandi',
        ];
    }

    /**
     * Whether the request is an intern (university + NIM) login.
     */
    public function isInternLogin(): bool
    {
        return $this->input('login_as') === 'intern';
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $authenticated = $this->isInternLogin()
            ? $this->authenticateIntern()
            : $this->authenticateAdmin();

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $this->errorField() => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Authenticate an administrator using NIK + password.
     *
     * The role is included as a credential so only admin accounts can sign in
     * through this path; interns have a null NIK and authenticate separately.
     */
    protected function authenticateAdmin(): bool
    {
        return Auth::attempt(
            [
                'nik' => (string) $this->input('nik'),
                'password' => (string) $this->input('password'),
                'role' => 'admin',
            ],
            $this->boolean('remember'),
        );
    }

    /**
     * Authenticate an intern using university + NIM + password.
     *
     * The intern is resolved by the (university, NIM) pair, then the linked
     * user account is signed in after verifying the password. Uses the query
     * builder and the hasher only (no raw SQL), so it stays portable.
     */
    protected function authenticateIntern(): bool
    {
        $intern = Intern::query()
            ->with('user')
            ->where('university_id', $this->integer('university_id'))
            ->where('nim', (string) $this->input('nim'))
            ->first();

        $user = $intern?->user;

        if ($user === null || $user->role !== 'intern') {
            // Run a dummy hash check to reduce user-enumeration timing leaks.
            Hash::check((string) $this->input('password'), '$2y$12$'.str_repeat('0', 53));

            return false;
        }

        if (! Hash::check((string) $this->input('password'), $user->password)) {
            return false;
        }

        Auth::login($user, $this->boolean('remember'));

        return true;
    }

    /**
     * The field that login errors should be attached to for the active mode.
     */
    protected function errorField(): string
    {
        return $this->isInternLogin() ? 'nim' : 'nik';
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
            $this->errorField() => trans('auth.throttle', [
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
        $identifier = $this->isInternLogin()
            ? $this->integer('university_id').':'.$this->input('nim')
            : (string) $this->input('nik');

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
