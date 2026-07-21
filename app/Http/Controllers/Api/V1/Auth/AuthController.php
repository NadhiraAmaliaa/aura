<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\UpdateAvatarRequest;
use App\Http\Requests\Api\V1\Auth\UpdateContactRequest;
use App\Http\Requests\Api\V1\Auth\UpdatePasswordRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Token-based authentication for the AURA mobile app (interns only).
 *
 * Issues Laravel Sanctum personal access tokens. The web/Inertia admin app is
 * unaffected: it continues to use the stateful session guard.
 */
class AuthController extends Controller
{
    /**
     * Authenticate an intern and issue a personal access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        $intern = $this->loadInternProfile($user)->intern;

        // Authorization gate: only interns whose internship is neither
        // deactivated nor finished may obtain a token. Upcoming interns are
        // allowed in so they can see their (not-yet-started) portal.
        if ($intern === null || ! $intern->canAccessPortal()) {
            return response()->json([
                'message' => $this->portalBlockedReason($intern),
            ], Response::HTTP_FORBIDDEN);
        }

        $deviceName = (string) ($request->input('device_name') ?: 'aura-mobile');

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($this->loadInternProfile($request->user()));
    }

    /**
     * Update the authenticated intern's contact details.
     *
     * The email lives on the user account while the phone belongs to the intern
     * profile, mirroring the web profile update flow.
     */
    public function updateContact(UpdateContactRequest $request): UserResource
    {
        $user = $request->user();

        $user->fill(['email' => $request->validated('email')]);
        $user->save();

        if ($user->intern !== null) {
            $user->intern->update(['phone' => $request->validated('phone')]);
        }

        return new UserResource($this->loadInternProfile($user->fresh()));
    }

    /**
     * Update the authenticated user's password.
     *
     * Leaves the current token valid so the mobile session is not interrupted
     * by the change.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }

    /**
     * Revoke the token used to authenticate the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Berhasil keluar.',
        ]);
    }

    /**
     * Replace the authenticated user's profile photo.
     *
     * Stores the upload on the public disk under `avatars` and removes the
     * previous file, if any, so orphaned photos do not accumulate.
     */
    public function updateAvatar(UpdateAvatarRequest $request): UserResource
    {
        $user = $request->user();
        $previousPath = $user->avatar_path;

        $path = $request->file('photo')->store('avatars', 'public');

        $user->update(['avatar_path' => $path]);

        if ($previousPath !== null && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return new UserResource($this->loadInternProfile($user->fresh()));
    }

    /**
     * Remove the authenticated user's profile photo, reverting to the default.
     */
    public function deleteAvatar(Request $request): UserResource
    {
        $user = $request->user();

        if ($user->avatar_path !== null) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return new UserResource($this->loadInternProfile($user->fresh()));
    }

    /**
     * Eager-load the intern profile together with the master-data relations the
     * mobile profile screen needs (university, study program, division and
     * program), so {@see UserResource} can expose their names.
     */
    protected function loadInternProfile(User $user): User
    {
        return $user->loadMissing([
            'intern.universityRef',
            'intern.studyProgram',
            'intern.divisionRef',
            'intern.internProgram',
        ]);
    }

    /**
     * Build the Indonesian reason a portal login is refused.
     */
    protected function portalBlockedReason(?Intern $intern): string
    {
        if ($intern !== null && $intern->isInactive()) {
            return 'Akun magang Anda telah dinonaktifkan. Silakan hubungi administrator.';
        }

        if ($intern !== null && $intern->hasEnded()) {
            $when = $intern->end_date ? ' (berakhir '.$intern->end_date->format('d-m-Y').')' : '';

            return 'Masa magang Anda telah berakhir'.$when.'.';
        }

        return 'Akun Anda tidak memiliki akses ke portal magang. Silakan hubungi administrator.';
    }
}
