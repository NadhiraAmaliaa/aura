<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengajuan Izin Baru – AURA</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 24px 12px; color: #1f2937; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
        .header { background: #1a5c35; color: #fff; padding: 28px 32px; }
        .header .brand { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 1px; }
        .header .tagline { margin: 4px 0 0; font-size: 13px; opacity: 0.85; }
        .body { padding: 32px; }
        .body p { line-height: 1.6; margin: 0 0 16px; font-size: 15px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 16px 0 24px; }
        .info-table td { padding: 10px 12px; font-size: 14px; vertical-align: top; }
        .info-table td.label { width: 38%; color: #555; font-weight: 600; border-right: 1px solid #e5e7eb; }
        .info-table tr:nth-child(odd) { background: #f9fafb; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; background: #fef3c7; color: #92400e; }
        .btn-wrap { text-align: center; margin: 8px 0 4px; }
        .btn { display: inline-block; padding: 12px 28px; background: #1a5c35; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 600; }
        .footer { padding: 18px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p class="brand">AURA</p>
            <p class="tagline">Sistem Absensi Peserta Magang</p>
        </div>
        <div class="body">
            <p>Yth. Supervisor/Mentor,</p>
            <p>
                Peserta magang di bawah pembinaan Anda telah mengajukan permohonan
                <strong>{{ $leaveRequest->typeLabel() }}</strong> dan menunggu peninjauan serta keputusan Anda.
            </p>

            <table class="info-table">
                <tr>
                    <td class="label">Nama Peserta</td>
                    <td>{{ $leaveRequest->user?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Pengajuan</td>
                    <td>{{ $leaveRequest->typeLabel() }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Izin</td>
                    <td>
                        {{ $leaveRequest->start_date->translatedFormat('d M Y') }}
                        &ndash; {{ $leaveRequest->end_date->translatedFormat('d M Y') }}
                        ({{ $leaveRequest->total_days }} HK)
                    </td>
                </tr>
                <tr>
                    <td class="label">Alasan</td>
                    <td>{{ $leaveRequest->reason }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td><span class="badge">Menunggu Persetujuan</span></td>
                </tr>
            </table>

            <div class="btn-wrap">
                <a href="{{ route('admin.leave-requests.show', $leaveRequest) }}" class="btn">Tinjau Pengajuan</a>
            </div>

            <p style="margin-top: 24px; font-size: 13px; color: #888;">
                Email ini dikirim otomatis oleh AURA. Mohon tidak membalas email ini.
            </p>
        </div>
        <div class="footer">
            AURA &mdash; Sistem Absensi Peserta Magang &middot; PTPN Holding
        </div>
    </div>
</body>
</html>
