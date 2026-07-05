<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
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
});
