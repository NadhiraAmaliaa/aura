<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLocationRequest;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display the attendance page with today's status and history.
     */
    public function index(): View
    {
        $userId = Auth::id();

        $todayAttendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->first();

        $history = Attendance::where('user_id', $userId)
            ->orderByDesc('attendance_date')
            ->paginate(10);

        return view('intern.attendance.index', compact('todayAttendance', 'history'));
    }

    /**
     * Record a check-in for today.
     */
    public function checkIn(AttendanceLocationRequest $request): RedirectResponse
    {
        $userId = Auth::id();

        $existing = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already checked in today.');
        }

        Attendance::create([
            'user_id' => $userId,
            'attendance_date' => today(),
            'check_in_time' => now()->format('H:i'),
            'check_in_latitude' => $request->validated('latitude'),
            'check_in_longitude' => $request->validated('longitude'),
            'status' => 'present',
        ]);

        return back()->with('status', 'Checked in successfully.');
    }

    /**
     * Record a check-out for today.
     */
    public function checkOut(AttendanceLocationRequest $request): RedirectResponse
    {
        $userId = Auth::id();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->first();

        if (! $attendance) {
            return back()->with('error', 'You must check in before checking out.');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'You have already checked out today.');
        }

        $attendance->update([
            'check_out_time' => now()->format('H:i'),
            'check_out_latitude' => $request->validated('latitude'),
            'check_out_longitude' => $request->validated('longitude'),
        ]);

        return back()->with('status', 'Checked out successfully.');
    }
}
