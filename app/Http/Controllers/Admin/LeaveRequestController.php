<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeaveRequestStatusRequest;
use App\Models\Attendance;
use App\Models\Division;
use App\Models\LeaveRequest;
use App\Services\Notifications\LeaveDecisionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly LeaveDecisionNotifier $decisionNotifier) {}

    /**
     * Display a filtered, paginated listing of all leave requests.
     *
     * Filtering and pagination are fully server-side so the page stays
     * responsive as the volume of leave requests grows. Every filter is
     * applied before pagination and preserved across page/per-page changes.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isSupervisor = $user->isSupervisor();

        $query = LeaveRequest::query()
            ->with(['user.intern.internProgram', 'user.intern.divisionRef'])
            ->orderByDesc('created_at');

        // Supervisors are locked to leave requests from their own division.
        if ($isSupervisor) {
            $query->whereHas('user.intern', fn ($i) => $i->where('division_id', $user->division_id));
        }

        // Division filter (admins only; supervisors are already scoped above).
        $divisionId = null;
        if (! $isSupervisor && $request->filled('division')) {
            $divisionId = (int) $request->query('division');
            $query->whereHas('user.intern', fn ($i) => $i->where('division_id', $divisionId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search): void {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search): void {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhereHas('intern', function ($i) use ($search): void {
                                $i->where('nim', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Leave period filters, matched against the request's own date range.
        $periodStart = $request->filled('period_start') ? $request->date('period_start') : null;
        $periodEnd = $request->filled('period_end') ? $request->date('period_end') : null;

        if ($periodStart !== null) {
            $query->whereDate('start_date', '>=', $periodStart);
        }

        if ($periodEnd !== null) {
            $query->whereDate('end_date', '<=', $periodEnd);
        }

        // Submission date filter (the day the request was created).
        $submittedOn = $request->filled('submitted_on') ? $request->date('submitted_on') : null;
        if ($submittedOn !== null) {
            $query->whereDate('created_at', $submittedOn);
        }

        $perPage = (int) $request->integer('perPage', 15);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $leaveRequests = $query->paginate($perPage)->withQueryString();

        return Inertia::render('admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'divisions' => $isSupervisor
                ? []
                : Division::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'status' => (string) $request->query('status', ''),
                'type' => (string) $request->query('type', ''),
                'search' => (string) $request->query('search', ''),
                'division' => $divisionId,
                'period_start' => $periodStart?->toDateString(),
                'period_end' => $periodEnd?->toDateString(),
                'submitted_on' => $submittedOn?->toDateString(),
                'perPage' => $request->filled('perPage') ? $perPage : null,
            ],
        ]);
    }

    /**
     * Display the detail of a single leave request.
     */
    public function show(Request $request, LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->load(['user.intern.internProgram', 'approver']);

        // Supervisors may only open leave requests from their own division.
        if ($request->user()->isSupervisor()
            && $leaveRequest->user?->intern?->division_id !== $request->user()->division_id) {
            abort(403, 'Unauthorized.');
        }

        return Inertia::render('admin/LeaveRequests/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    /**
     * Approve a pending leave request.
     */
    public function approve(UpdateLeaveRequestStatusRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        return $this->decide($request, $leaveRequest, 'approved', 'Pengajuan telah disetujui.');
    }

    /**
     * Reject a pending leave request.
     */
    public function reject(UpdateLeaveRequestStatusRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        return $this->decide($request, $leaveRequest, 'rejected', 'Pengajuan telah ditolak.');
    }

    /**
     * Apply an approval decision to a leave request.
     */
    private function decide(
        UpdateLeaveRequestStatusRequest $request,
        LeaveRequest $leaveRequest,
        string $status,
        string $message,
    ): RedirectResponse {
        // A supervisor may only decide on leave requests from interns in the
        // division they manage.
        $leaveRequest->loadMissing('user.intern');

        if ($leaveRequest->user?->intern?->division_id !== $request->user()->division_id) {
            abort(403, 'Unauthorized.');
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        // The status change and the attendance synchronisation must succeed or
        // fail together: if the attendance sync throws, the approval is rolled
        // back so the request never ends up approved without matching records.
        DB::transaction(function () use ($leaveRequest, $status, $request): void {
            $leaveRequest->update([
                'status' => $status,
                'admin_note' => $request->validated('admin_note'),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            if ($status === 'approved') {
                $this->syncAttendanceForApprovedLeave($leaveRequest);
            }
        });

        // Notify the intern only after the decision has been committed. Push
        // delivery is best-effort and must never turn a successful decision
        // into a failed request.
        $this->notifyDecision($leaveRequest);

        return redirect()
            ->route('admin.leave-requests.show', $leaveRequest)
            ->with('status', $message);
    }

    /**
     * Send the approve/reject push notification to the requesting intern.
     *
     * Best-effort: any failure here is logged and never propagated, so the
     * decision the supervisor just made always stands.
     */
    private function notifyDecision(LeaveRequest $leaveRequest): void
    {
        try {
            $this->decisionNotifier->notify($leaveRequest);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi keputusan pengajuan izin.', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reflect an approved leave on attendance records for each covered date.
     *
     * A record is only created when none exists for that date. Existing
     * attendance records are never overwritten — corrections to those must
     * go through the authorized administrative process.
     */
    private function syncAttendanceForApprovedLeave(LeaveRequest $leaveRequest): void
    {
        $status = Attendance::statusForLeaveType($leaveRequest->type);
        $intern = $leaveRequest->user?->intern;

        $date = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $endDate = Carbon::parse($leaveRequest->end_date)->startOfDay();

        while ($date->lessThanOrEqualTo($endDate)) {
            // Never record leave attendance for days outside the intern's
            // internship period; those days are not part of the recap window.
            if ($intern !== null && ! $intern->isWithinPeriod($date)) {
                $date->addDay();

                continue;
            }

            $exists = Attendance::where('user_id', $leaveRequest->user_id)
                ->whereDate('attendance_date', $date->toDateString())
                ->exists();

            if (! $exists) {
                Attendance::create([
                    'user_id' => $leaveRequest->user_id,
                    'attendance_date' => $date->toDateString(),
                    'status' => $status,
                    'notes' => 'Otomatis dari pengajuan '.$leaveRequest->request_number.'.',
                ]);
            }

            $date->addDay();
        }
    }
}
