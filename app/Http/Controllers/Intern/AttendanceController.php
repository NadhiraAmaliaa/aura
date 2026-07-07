<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLocationRequest;
use App\Http\Requests\CheckInRequest;
use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
    ) {}

    /**
     * Display the attendance page with today's status and history.
     */
    public function index(): Response
    {
        $userId = Auth::id();

        $todayAttendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->first();

        $todayLeave = LeaveRequest::approvedCovering($userId, today())->first();

        $expectedCheckOut = Attendance::expectedCheckOutTime(today());

        $history = Attendance::where('user_id', $userId)
            ->orderByDesc('attendance_date')
            ->paginate(10);

        return Inertia::render('intern/Attendance/Index', [
            'todayAttendance' => $todayAttendance,
            'todayLeave' => $todayLeave,
            'expectedCheckOut' => $expectedCheckOut,
            'history' => $history, 
        ]);
    }

    /**
     * Record a check-in for today.
     */
    public function checkIn(CheckInRequest $request): RedirectResponse
    {
        try {
            $this->attendance->checkIn(
                Auth::user(),
                $request->validated('work_mode'),
                $request->validated('latitude'),
                $request->validated('longitude'),
            );
        } catch (AttendanceException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Check In berhasil.');
    }

    /**
     * Record a check-out for today.
     */
    public function checkOut(AttendanceLocationRequest $request): RedirectResponse
    {
        try {
            $this->attendance->checkOut(
                Auth::user(),
                $request->validated('latitude'),
                $request->validated('longitude'),
            );
        } catch (AttendanceException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Check Out berhasil.');
    }
}
