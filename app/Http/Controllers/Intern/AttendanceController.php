<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLocationRequest;
use App\Http\Requests\CheckInRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
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

        $todayLeave = LeaveRequest::approvedCovering($userId, today())->first();

        $expectedCheckOut = Attendance::expectedCheckOutTime(today());

        $history = Attendance::where('user_id', $userId)
            ->orderByDesc('attendance_date')
            ->paginate(10);

        return view('intern.attendance.index', compact(
            'todayAttendance',
            'todayLeave',
            'expectedCheckOut',
            'history'
        ));
    }

    /**
     * Record a check-in for today.
     */
    public function checkIn(CheckInRequest $request): RedirectResponse
    {
        $userId = Auth::id();

        $todayLeave = LeaveRequest::approvedCovering($userId, today())->first();

        if ($todayLeave) {
            return back()->with(
                'error',
                'Hari ini Anda sedang dalam masa '.$todayLeave->typeLabel().' yang telah disetujui, sehingga tidak dapat melakukan Check In.'
            );
        }

        $existing = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', today())
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah melakukan Check In hari ini.');
        }

        $workMode = $request->validated('work_mode');
        $now = now();

        if (! Attendance::isCheckInAllowed($now, $workMode)) {
            $deadline = Attendance::checkInDeadline($now, $workMode);

            return back()->with(
                'error',
                'Check In untuk mode ini hanya dapat dilakukan hingga pukul '.$deadline.'. Waktu Check In telah terlewati.'
            );
        }

        // NOTE: WFO check-ins will later be validated against the office
        // location and radius (geofencing). See Attendance::requiresGeofence().
        // The geofence enforcement is intentionally not implemented yet.

        Attendance::create([
            'user_id' => $userId,
            'attendance_date' => today(),
            'check_in_time' => $now->format('H:i'),
            'check_in_latitude' => $request->validated('latitude'),
            'check_in_longitude' => $request->validated('longitude'),
            'status' => Attendance::determineStatus($now, $workMode),
            'work_mode' => $workMode,
        ]);

        return back()->with('status', 'Check In berhasil.');
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

        if (! $attendance || ! $attendance->check_in_time) {
            return back()->with('error', 'Anda harus Check In terlebih dahulu sebelum Check Out.');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'Anda sudah melakukan Check Out hari ini.');
        }

        $now = now();
        $checkInMoment = $now->copy()->setTimeFromTimeString($attendance->check_in_time->format('H:i:s'));

        if ($now->lessThanOrEqualTo($checkInMoment)) {
            return back()->with('error', 'Waktu Check Out harus setelah waktu Check In.');
        }

        $attendance->update([
            'check_out_time' => $now->format('H:i'),
            'check_out_latitude' => $request->validated('latitude'),
            'check_out_longitude' => $request->validated('longitude'),
        ]);

        return back()->with('status', 'Check Out berhasil.');
    }
}
