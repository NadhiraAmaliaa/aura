<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LeaveRequestPdfController extends Controller
{
    /**
     * Stream a printable PDF of an approved leave request.
     *
     * Accessible only by an admin or the request owner.
     */
    public function __invoke(LeaveRequest $leaveRequest): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless(
            $user->isAdmin() || $leaveRequest->user_id === $user->id,
            Response::HTTP_FORBIDDEN
        );

        abort_unless($leaveRequest->status === 'approved', Response::HTTP_FORBIDDEN);

        $leaveRequest->load(['user.intern.internProgram', 'approver']);

        $pdf = Pdf::loadView('leave-requests.pdf', compact('leaveRequest'))
            ->setPaper('letter');

        return $pdf->stream("pengajuan-{$leaveRequest->request_number}.pdf");
    }
}
