<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
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
    public function store(StoreLeaveRequestRequest $request, LeaveRequestService $service): RedirectResponse
    {
        $service->submit(
            Auth::user(),
            $request->validated(),
            $request->file('evidence'),
        );

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
}
