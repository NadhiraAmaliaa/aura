<?php

namespace App\Http\Controllers\Api\V1\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuratPulangCepatRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Streams a "Surat Izin Pulang Sebelum Waktunya" (early-leave letter) PDF for
 * the authenticated intern.
 *
 * Stateless by design: nothing is persisted (no model, history, workflow, or
 * document number). The letter is rendered on demand from the intern's stored
 * identity plus the submitted early-leave details, then streamed back for the
 * mobile app to print or save.
 */
class SuratPulangCepatController extends Controller
{
    public function __invoke(StoreSuratPulangCepatRequest $request): Response
    {
        $user = $request->user();
        $user->loadMissing([
            'intern.internProgram',
            'intern.universityRef',
            'intern.studyProgram',
            'intern.divisionRef',
        ]);
        $intern = $user->intern;

        // Identity comes from the authenticated account, never the request body.
        // Prefer the master-data relations, falling back to the legacy free-text
        // columns so older records still render.
        $identity = [
            'name' => $user->name,
            'nim' => $intern?->nim ?? '-',
            'program' => $intern?->internProgram?->name ?? '-',
            'university' => $intern?->universityRef?->name ?? $intern?->university ?? '-',
            'major' => $intern?->studyProgram?->name ?? $intern?->major ?? '-',
            'division' => $intern?->divisionRef?->name ?? $intern?->division ?? '-',
        ];

        $earlyLeaveDate = Carbon::parse($request->validated('early_leave_date'));
        $leaveTime = $request->validated('leave_time');
        $reason = $request->validated('reason');

        // City for the closing signature block. Mirrors the approved-leave
        // letter's convention; kept as a constant until SDM specifies otherwise.
        $city = 'Jakarta';
        $createdDate = Carbon::today();

        $pdf = Pdf::loadView('surat-pulang-cepat.pdf', [
            'identity' => $identity,
            'earlyLeaveDate' => $earlyLeaveDate,
            'leaveTime' => $leaveTime,
            'reason' => $reason,
            'city' => $city,
            'createdDate' => $createdDate,
            'logos' => $this->embedLogos(),
        ])->setPaper('letter');

        $fileName = 'surat-izin-pulang-'.$identity['nim'].'-'.$earlyLeaveDate->format('Ymd').'.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Embed the letterhead logos as base64 data URIs (DomPDF cannot resolve web
     * URLs). Duplicated from the approved-leave letter so that controller stays
     * untouched; the two letters intentionally share the same letterhead.
     *
     * @return array<string, string|null>
     */
    private function embedLogos(): array
    {
        return collect([
            'danantara' => 'images/logos/danantara-indonesia.svg',
            'holding' => 'images/logos/logo-holding-perkebunan-nusantara.png',
            'ptpn' => 'images/logos/logo-ptpn.png',
        ])->map(function (string $relative): ?string {
            $path = public_path($relative);

            if (! file_exists($path)) {
                return null;
            }

            return 'data:'.mime_content_type($path).';base64,'.base64_encode(file_get_contents($path));
        })->all();
    }
}
