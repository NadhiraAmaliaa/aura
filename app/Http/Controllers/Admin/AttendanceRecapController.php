<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceRecapExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRecapRequest;
use App\Models\InternProgram;
use App\Services\AttendanceRecapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AttendanceRecapController extends Controller
{
    /**
     * Display the attendance recap for a selected reporting period.
     */
    public function index(AttendanceRecapRequest $request, AttendanceRecapService $service): View
    {
        [$startDate, $endDate, $programId] = $this->resolveFilters($request);

        $recap = $service->build($startDate, $endDate, $programId);
        $programs = InternProgram::orderBy('name')->get();

        return view('admin.attendances.recap', compact('recap', 'programs'));
    }

    /**
     * Export the attendance recap as an Excel spreadsheet.
     */
    public function exportExcel(AttendanceRecapRequest $request, AttendanceRecapService $service): BinaryFileResponse
    {
        [$startDate, $endDate, $programId] = $this->resolveFilters($request);

        $recap = $service->build($startDate, $endDate, $programId);
        $programName = $programId ? InternProgram::find($programId)?->name : null;

        $fileName = 'rekap-absensi-'.$startDate->format('Ymd').'-'.$endDate->format('Ymd').'.xlsx';

        return Excel::download(new AttendanceRecapExport($recap, $programName), $fileName);
    }

    /**
     * Export the attendance recap as a printable PDF.
     */
    public function exportPdf(AttendanceRecapRequest $request, AttendanceRecapService $service): Response
    {
        [$startDate, $endDate, $programId] = $this->resolveFilters($request);

        $recap = $service->build($startDate, $endDate, $programId);
        $programName = $programId ? InternProgram::find($programId)?->name : null;

        $pdf = Pdf::loadView('admin.attendances.recap-pdf', compact('recap', 'programName'))
            ->setPaper('a4', 'landscape');

        $fileName = 'rekap-absensi-'.$startDate->format('Ymd').'-'.$endDate->format('Ymd').'.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Resolve the reporting filters, applying sensible defaults.
     *
     * Shared by the page and both exports so the exported data always
     * matches the table shown on the web page.
     *
     * @return array{0: Carbon, 1: Carbon, 2: int|null}
     */
    private function resolveFilters(AttendanceRecapRequest $request): array
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

        return [$startDate, $endDate, $programId];
    }
}
