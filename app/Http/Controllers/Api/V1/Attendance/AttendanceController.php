<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Exceptions\AttendanceException;
use App\Http\Requests\Api\V1\Attendance\CheckInRequest;
use App\Http\Resources\Api\V1\Attendance\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\InternAttendanceSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Attendance endpoints for the AURA mobile app (interns only).
 *
 * Reads (dashboard, history) plus the check-in write action. Check-out lands
 * in a later slice.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private readonly InternAttendanceSummaryService $summary,
        private readonly AttendanceService $attendance,
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
     * Return the intern's attendance records, most recent first, paginated.
     *
     * Accepts an optional `page` query parameter (Laravel's paginator handles
     * it) and a `per_page` value clamped to a sane range. The response wraps
     * the items with a compact pagination block the mobile client can page on.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = $this->resolvePerPage($request->query('per_page'));

        $records = Attendance::query()
            ->where('user_id', $user->id)
            ->orderByDesc('attendance_date')
            ->paginate($perPage);

        return response()->json([
            'data' => [
                'items' => AttendanceResource::collection($records->items()),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'last_page' => $records->lastPage(),
                    'has_more' => $records->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Record today's check-in.
     *
     * Delegates all business rules to [AttendanceService]; a rule violation is
     * translated into the matching HTTP status with a user-safe message. On
     * success returns the created record so the client can update its UI
     * without an extra round-trip.
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendance->checkIn(
                $request->user(),
                $request->validated('work_mode'),
                $request->validated('latitude'),
                $request->validated('longitude'),
            );
        } catch (AttendanceException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'message' => 'Check In berhasil.',
            'data' => new AttendanceResource($attendance),
        ], 201);
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

    /**
     * Clamp the page size to a sane range, defaulting to 15 records per page.
     */
    private function resolvePerPage(mixed $value): int
    {
        $perPage = is_numeric($value) ? (int) $value : 15;

        return max(1, min($perPage, 50));
    }
}
