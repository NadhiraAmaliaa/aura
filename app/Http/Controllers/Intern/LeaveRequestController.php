<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LeaveRequestController extends Controller
{
    /**
     * Display the intern's own leave request history.
     */
    public function index(): View
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('intern.leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Show the form to create a new leave request.
     */
    public function create(): View
    {
        return view('intern.leave-requests.create');
    }

    /**
     * Store a new leave request.
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $data['type'],
            'reason' => $data['reason'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'contact_phone' => $data['contact_phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('intern.leave-requests.index')
            ->with('status', 'Pengajuan berhasil dikirim.');
    }

    /**
     * Display the detail of a single leave request owned by the intern.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        abort_if((int) $leaveRequest->user_id !== (int) Auth::id(), Response::HTTP_FORBIDDEN);

        $leaveRequest->load('approver');

        return view('intern.leave-requests.show', compact('leaveRequest'));
    }
}
