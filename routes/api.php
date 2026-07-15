<?php

use App\Http\Controllers\Api\V1\Attendance\AttendanceController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Leave\LeaveRequestController;
use App\Http\Controllers\Api\V1\UniversityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Token-based endpoints for the AURA mobile app. These are stateless and
| authenticated with Sanctum personal access tokens. The web/Inertia admin
| app is served separately from routes/web.php and is not affected here.
|
*/

Route::prefix('v1')->group(function (): void {
    // Public lookup: feeds the mobile login screen's university picker. Only
    // universities with at least one registered intern are returned. Lightly
    // throttled as it is unauthenticated.
    Route::get('universities', [UniversityController::class, 'index'])
        ->middleware('throttle:60,1');

    Route::prefix('auth')->group(function (): void {
        // Public: intern login. Throttled as a second line of defence on top
        // of the per-credential rate limiting inside the form request.
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');

        // Protected: require a valid Sanctum token.
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // Protected intern-facing feature endpoints.
    Route::middleware('auth:sanctum')->group(function (): void {
        // Attendance dashboard: today's snapshot + monthly recap (read-only).
        Route::get('attendance/dashboard', [AttendanceController::class, 'dashboard']);

        // Attendance history: paginated list of past records, newest first.
        Route::get('attendance/history', [AttendanceController::class, 'history']);

        // Attendance check-in: record today's arrival (work mode + coords).
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);

        // Attendance check-out: record today's departure (coords only).
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

        // Active office locations for WFO geofence pre-validation / map display.
        Route::get('attendance/locations', [AttendanceController::class, 'locations']);

        // Leave requests (izin / sakit): the intern's own pending queue,
        // processed history, and single-request detail. Read-only for now;
        // approval stays on the admin web app.
        Route::get('leave-requests', [LeaveRequestController::class, 'index']);
        Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
    });
});
