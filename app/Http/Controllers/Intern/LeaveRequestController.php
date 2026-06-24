<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class LeaveRequestController extends Controller
{
    /**
     * Display the intern's own leave request history.
     */
    public function index(): InertiaResponse
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('intern/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Show the form to create a new leave request.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('intern/LeaveRequests/Create');
    }

    /**
     * Store a new leave request.
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        // Store the uploaded evidence (PDF/image) on the public disk so it can
        // be reviewed later by the admin/supervisor.
        $evidencePath = $request->hasFile('evidence')
            ? $request->file('evidence')->store('leave-evidence', 'public')
            : null;

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'reason' => $data['reason'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => LeaveRequest::calculateWorkingDays($startDate, $endDate),
            'contact_phone' => $data['contact_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'evidence_path' => $evidencePath,
            'status' => 'pending',
        ]);

        // Notify the division supervisor (if one exists and has an email).
        $this->notifySupervisor($leaveRequest);

        return redirect()
            ->route('intern.leave-requests.index')
            ->with('status', 'Pengajuan berhasil dikirim.');
    }

    /**
     * Display the detail of a single leave request owned by the intern.
     */
    public function show(LeaveRequest $leaveRequest): InertiaResponse
    {
        abort_if((int) $leaveRequest->user_id !== (int) Auth::id(), Response::HTTP_FORBIDDEN);

        $leaveRequest->load('approver');

        return Inertia::render('intern/LeaveRequests/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    /**
     * Find and e-mail the division supervisor for this leave request.
     */
    private function notifySupervisor(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->load('user.intern');

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

        $supervisor->notify(new LeaveRequestSubmittedNotification($leaveRequest));
    }
}
