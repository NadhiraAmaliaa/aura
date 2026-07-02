<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the intern dashboard with today's attendance and leave status.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $todayAttendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->first();

        $todayLeave = LeaveRequest::approvedCovering($userId, today())->first();

        return Inertia::render('intern/Dashboard', [
            'todayAttendance' => $todayAttendance,
            'todayLeave' => $todayLeave,
            'expectedCheckOut' => Attendance::expectedCheckOutTime(today()),
        ]);
    }
}
