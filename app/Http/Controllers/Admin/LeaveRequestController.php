<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeaveRequestStatusRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    /**
     * Display a filtered, paginated listing of all leave requests.
     */
    public function index(Request $request): View
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

        return view('admin.leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Display the detail of a single leave request.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['user.intern.internProgram', 'approver']);

        return view('admin.leave-requests.show', compact('leaveRequest'));
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

        return redirect()
            ->route('admin.leave-requests.show', $leaveRequest)
            ->with('status', $message);
    }
}
