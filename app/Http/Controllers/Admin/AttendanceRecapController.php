<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRecapRequest;
use App\Models\InternProgram;
use App\Services\AttendanceRecapService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceRecapController extends Controller
{
    /**
     * Display the attendance recap for a selected reporting period.
     */
    public function index(AttendanceRecapRequest $request, AttendanceRecapService $service): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->validated('start_date'))
            : Carbon::now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->validated('end_date'))
            : Carbon::now();

        $programId = $request->filled('program')
            ? (int) $request->validated('program')
            : null;

        $recap = $service->build($startDate, $endDate, $programId);
        $programs = InternProgram::orderBy('name')->get();

        return view('admin.attendances.recap', compact('recap', 'programs'));
    }
}
