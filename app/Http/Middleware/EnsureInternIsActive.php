<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternIsActive
{
    /**
     * Ensure an authenticated intern still has an active internship.
     *
     * This runs on every intern-portal request, so a status change made by an
     * administrator (deactivation / completion) or the natural end of the
     * internship period takes effect on the intern's next request: the session
     * is terminated and the intern is returned to the login screen with an
     * explanation. Interns whose period has not started yet are still allowed
     * in (attendance itself is blocked separately until the start date).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isIntern()) {
            $intern = $user->intern;

            if ($intern === null || ! $intern->canAccessPortal()) {
                $message = $intern?->attendanceBlockReason()
                    ?? 'Profil magang Anda belum lengkap. Silakan hubungi administrator.';

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', $message);
            }
        }

        return $next($request);
    }
}
