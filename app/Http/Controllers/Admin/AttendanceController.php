<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceReportRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Division;
use App\Models\InternProgram;
use App\Services\AttendanceReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceReportService $report)
    {
    }

    /**
     * Display the daily attendance report for administrators.
     */
    public function index(AttendanceReportRequest $request): Response
    {
        $date = $this->resolveDate($request);
        $programId = $request->integer('program') ?: null;
        $divisionId = $request->integer('division') ?: null;

        return Inertia::render('admin/Attendances/Report', [
            'report' => $this->report->build($date, $programId, $divisionId),
            'programs' => InternProgram::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'date' => $date->toDateString(),
                'program' => $programId,
                'division' => $divisionId,
            ],
        ]);
    }

    /**
     * Export the daily attendance report as a spreadsheet.
     */
    public function export(AttendanceReportRequest $request): BinaryFileResponse
    {
        $date = $this->resolveDate($request);
        $programId = $request->integer('program') ?: null;
        $divisionId = $request->integer('division') ?: null;

        $report = $this->report->build($date, $programId, $divisionId);

        $programName = $programId
            ? InternProgram::whereKey($programId)->value('name')
            : null;
        $divisionName = $divisionId
            ? Division::whereKey($divisionId)->value('name')
            : null;

        $fileName = 'reporting-absensi-'.$date->toDateString().'.xlsx';

        return Excel::download(
            new AttendanceReportExport($report, $programName, $divisionName),
            $fileName
        );
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
            ->route('admin.attendances.index', ['date' => $attendance->attendance_date->toDateString()])
            ->with('status', 'Status absensi berhasil diperbarui.');
    }

    /**
     * Resolve the report date from the request, defaulting to today.
     */
    private function resolveDate(AttendanceReportRequest $request): Carbon
    {
        $date = $request->validated('date');

        return $date ? Carbon::parse($date)->startOfDay() : Carbon::today();
    }
}
