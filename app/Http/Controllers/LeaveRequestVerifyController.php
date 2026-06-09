<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\View\View;

class LeaveRequestVerifyController extends Controller
{
    /**
     * Display the public verification page for a leave request.
     *
     * No authentication required — this URL is printed in the QR code.
     */
    public function __invoke(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['user.intern.internProgram', 'approver']);

        $isValid = $leaveRequest->status === 'approved';

        return view('leave-requests.verify', compact('leaveRequest', 'isValid'));
    }
}
