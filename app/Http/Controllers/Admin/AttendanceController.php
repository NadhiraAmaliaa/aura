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
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $programId = $request->integer('program') ?: null;
        $divisionId = $request->integer('division') ?: null;

        return Inertia::render('admin/Attendances/Report', [
            'report' => $this->report->build($startDate, $endDate, $programId, $divisionId),
            'programs' => InternProgram::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'program' => $programId,
                'division' => $divisionId,
            ],
        ]);
    }

    /**
     * Export the attendance report as a spreadsheet.
     */
    public function export(AttendanceReportRequest $request): BinaryFileResponse
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $programId = $request->integer('program') ?: null;
        $divisionId = $request->integer('division') ?: null;

        $report = $this->report->build($startDate, $endDate, $programId, $divisionId);

        $fileName = 'laporan-absensi-'.$startDate->toDateString().'-sd-'.$endDate->toDateString().'.xlsx';

        return Excel::download(
            new AttendanceReportExport($report),
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
            ->route('admin.attendances.index', [
                'start_date' => $attendance->attendance_date->toDateString(),
                'end_date' => $attendance->attendance_date->toDateString(),
            ])
            ->with('status', 'Status absensi berhasil diperbarui.');
    }

    /**
     * Resolve start and end dates from the request, both defaulting to today.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(AttendanceReportRequest $request): array
    {
        $today = Carbon::today();
        $start = $request->validated('start_date')
            ? Carbon::parse($request->validated('start_date'))->startOfDay()
            : $today->copy()->startOfDay();
        $end = $request->validated('end_date')
            ? Carbon::parse($request->validated('end_date'))->startOfDay()
            : $today->copy()->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        return [$start, $end];
    }
}
