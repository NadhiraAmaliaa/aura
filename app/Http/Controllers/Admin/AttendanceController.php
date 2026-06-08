<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\InternProgram;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display a filtered, paginated listing of all attendance records.
     */
    public function index(Request $request): View
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

        if ($request->filled('program')) {
            $query->whereHas('user.intern', function ($q) use ($request): void {
                $q->where('intern_program_id', $request->program);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('user.intern', function ($q) use ($search): void {
                $q->where('nim', 'like', "%{$search}%");
            });
        }

        $attendances = $query->paginate(15)->withQueryString();
        $programs = InternProgram::orderBy('name')->get();

        return view('admin.attendances.index', compact('attendances', 'programs'));
    }
}
