<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Intern;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

        $intern = $user->loadMissing('intern')->intern;

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
        return new UserResource($request->user()->loadMissing('intern'));
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
