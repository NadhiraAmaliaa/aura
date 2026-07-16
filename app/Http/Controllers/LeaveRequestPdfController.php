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
     * Accessible by an admin, the request owner, or the supervisor of the
     * intern's division.
     */
    public function __invoke(LeaveRequest $leaveRequest): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $leaveRequest->loadMissing('user.intern');

        // Compare via Eloquent keys, not raw attributes: SQL Server returns
        // foreign keys as strings ($leaveRequest->user_id === '3') while the
        // model's primary key is cast to int ($user->id === 3), so a strict
        // `===` on the raw columns would wrongly fail for the actual owner.
        $isOwner = $user->is($leaveRequest->user);
        $internDivisionId = $leaveRequest->user?->intern?->division_id;
        $isDivisionSupervisor = $user->isSupervisor()
            && $internDivisionId !== null
            && (int) $internDivisionId === (int) $user->division_id;

        abort_unless(
            $user->isAdmin() || $isOwner || $isDivisionSupervisor,
            Response::HTTP_FORBIDDEN
        );

        abort_unless($leaveRequest->status === 'approved', Response::HTTP_FORBIDDEN);

        $leaveRequest->load(['user.intern.internProgram', 'approver']);

        // Render plain-text signature QR codes (full names only). These are a
        // visual stand-in for handwritten signatures — no verification URL.
        // SVG backend requires no PHP extensions (Imagick/GD not needed).
        $makeQr = static fn (string $text): string => base64_encode(
            QrCode::format('svg')->size(120)->errorCorrection('M')->generate($text)
        );

        $supervisorName = $leaveRequest->approver?->name ?? '-';
        $internName = $leaveRequest->user?->name ?? '-';

        $supervisorQr = $makeQr($supervisorName);
        $internQr = $makeQr($internName);

        // DomPDF cannot resolve web URLs; embed logos as base64 data URIs.
        $logos = collect([
            'danantara'  => 'images/logos/danantara-indonesia.svg',
            'holding'    => 'images/logos/logo-holding-perkebunan-nusantara.png',
            'ptpn'       => 'images/logos/logo-ptpn.png',
        ])->map(function (string $relative): ?string {
            $path = public_path($relative);

            if (! file_exists($path)) {
                return null;
            }

            $mime = mime_content_type($path);
            $data = base64_encode(file_get_contents($path));

            return "data:{$mime};base64,{$data}";
        });

        $pdf = Pdf::loadView('leave-requests.pdf', compact('leaveRequest', 'supervisorQr', 'internQr', 'logos'))
            ->setPaper('letter');

        return $pdf->stream("pengajuan-{$leaveRequest->request_number}.pdf");
    }
}

