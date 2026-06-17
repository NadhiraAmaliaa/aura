<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\InternProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Display a filtered, paginated listing of all attendance records.
     */
    public function index(Request $request): Response
    {
        $query = Attendance::query()
            ->with(['user.intern.internProgram'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_time');

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_mode')) {
            $query->where('work_mode', $request->work_mode);
        }

        if ($request->filled('program')) {
            $query->whereHas('user.intern', function ($q) use ($request): void {
                $q->where('intern_program_id', $request->program);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('user.intern', function ($q) use ($search): void {
                $q->where('nim', 'like', "%{$search}%");
            });
        }

        $attendances = $query->paginate(15)->withQueryString();
        $programs = InternProgram::orderBy('name')->get();

        return Inertia::render('admin/Attendances/Index', [
            'attendances' => $attendances,
            'programs' => $programs,
            'filters' => $request->only(['date', 'status', 'work_mode', 'program', 'search']),
        ]);
    }

    /**
     * Show the form to correct a single attendance record's status.
     */
    public function edit(Attendance $attendance): Response
    {
        $attendance->load(['user.intern.internProgram']);

        return Inertia::render('admin/Attendances/Edit', [
            'attendance' => $attendance,
        ]);
    }

    /**
     * Apply an authorized administrative status correction.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update([
            'status' => $request->validated('status'),
            'work_mode' => $request->validated('work_mode'),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()
            ->route('admin.attendances.index')
            ->with('status', 'Status absensi berhasil diperbarui.');
    }
}
