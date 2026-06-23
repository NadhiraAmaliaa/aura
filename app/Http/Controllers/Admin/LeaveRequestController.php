<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeaveRequestStatusRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestDecidedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    /**
     * Display a filtered, paginated listing of all leave requests.
     */
    public function index(Request $request): Response
    {
        $query = LeaveRequest::query()
            ->with(['user.intern.internProgram'])
            ->orderByDesc('created_at');

        // Supervisors only see leave requests from interns in their division.
        if ($request->user()->isSupervisor()) {
            $divisionId = $request->user()->division_id;
            $query->whereHas('user.intern', fn ($i) => $i->where('division_id', $divisionId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
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

        $perPage = (int) $request->integer('perPage', 15);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $leaveRequests = $query->paginate($perPage)->withQueryString();

        return Inertia::render('admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'perPage' => $perPage,
            'filters' => $request->only(['status', 'type', 'search']),
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
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $leaveRequest->update([
            'status' => $status,
            'admin_note' => $request->validated('admin_note'),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        if ($status === 'approved') {
            $this->syncAttendanceForApprovedLeave($leaveRequest);
        }

        // Notify the division supervisor of the decision.
        $this->notifySupervisor($leaveRequest->fresh(['user.intern']));

        return redirect()
            ->route('admin.leave-requests.show', $leaveRequest)
            ->with('status', $message);
    }

    /**
     * Find and e-mail the division supervisor about the decision.
     */
    private function notifySupervisor(?LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest) {
            return;
        }

        $divisionId = $leaveRequest->user?->intern?->division_id;

        if (! $divisionId) {
            return;
        }

        $supervisor = User::where('role', 'supervisor')
            ->where('division_id', $divisionId)
            ->whereNotNull('email')
            ->where('is_active', true)
            ->first();

        if (! $supervisor) {
            return;
        }

        $supervisor->notify(new LeaveRequestDecidedNotification($leaveRequest));
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
