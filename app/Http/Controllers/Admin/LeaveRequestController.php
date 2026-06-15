<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeaveRequestStatusRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
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
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $leaveRequests = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'filters' => $request->only(['status', 'type', 'search']),
        ]);
    }

    /**
     * Display the detail of a single leave request.
     */
    public function show(LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->load(['user.intern.internProgram', 'approver']);

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

        return redirect()
            ->route('admin.leave-requests.show', $leaveRequest)
            ->with('status', $message);
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

        $date = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $endDate = Carbon::parse($leaveRequest->end_date)->startOfDay();

        while ($date->lessThanOrEqualTo($endDate)) {
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
