<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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

        $verifyUrl = route('leave-requests.verify', $leaveRequest);

        // Generate QR code as base64-encoded SVG for embedding in the PDF.
        // SVG backend requires no PHP extensions (Imagick/GD not needed).
        $qrCode = base64_encode(
            QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
        );

        $pdf = Pdf::loadView('leave-requests.pdf', compact('leaveRequest', 'qrCode'))
            ->setPaper('letter');

        return $pdf->stream("pengajuan-{$leaveRequest->request_number}.pdf");
    }
}

