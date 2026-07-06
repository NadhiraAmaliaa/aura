<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Attendance\AttendanceResource;
use App\Services\InternAttendanceSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Read-only attendance endpoints for the AURA mobile app (interns only).
 *
 * The write actions (check-in / check-out) land in a later slice; this
 * controller currently only exposes the dashboard snapshot.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private readonly InternAttendanceSummaryService $summary,
    ) {}

    /**
     * Return today's attendance snapshot plus the monthly recap.
     *
     * Accepts an optional `month=YYYY-MM` query parameter to drive the recap;
     * it defaults to the current month. The "today" card is always today.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $month = $this->resolveMonth($request->query('month'));

        $today = $this->summary->todaySnapshot($user);
        $summary = $this->summary->monthlySummary($user, $month);

        return response()->json([
            'data' => [
                'today' => [
                    'date' => $today['date'],
                    'is_working_day' => $today['is_working_day'],
                    'work_hours' => $today['work_hours'],
                    'attendance' => $today['attendance'] === null
                        ? null
                        : new AttendanceResource($today['attendance']),
                    'leave' => $today['leave'],
                ],
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Resolve the recap month from a `YYYY-MM` string, falling back to the
     * current month when the value is missing or malformed.
     */
    private function resolveMonth(mixed $value): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
            try {
                return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            } catch (\Throwable) {
                // Fall through to the current month on an invalid date.
            }
        }

        return Carbon::today()->startOfMonth();
    }
}
