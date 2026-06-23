<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keputusan Pengajuan Izin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #1a5c35; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; font-size: 13px; opacity: 0.85; }
        .body { padding: 32px; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .info-table td { padding: 8px 12px; font-size: 14px; vertical-align: top; }
        .info-table td.label { width: 38%; color: #555; font-weight: bold; border-right: 1px solid #e5e7eb; }
        .info-table tr:nth-child(odd) { background: #f9fafb; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .footer { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                @if($leaveRequest->status === 'approved')
                    Pengajuan Izin Disetujui ✓
                @else
                    Pengajuan Izin Ditolak ✗
                @endif
            </h1>
            <p>Notifikasi untuk Supervisor Divisi</p>
        </div>
        <div class="body">
            <p>Yth. Supervisor,</p>
            <p>
                Permohonan izin / sakit peserta magang berikut telah mendapatkan keputusan dari administrator.
            </p>

            <table class="info-table">
                <tr>
                    <td class="label">Nomor Pengajuan</td>
                    <td>{{ $leaveRequest->request_number }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Peserta</td>
                    <td>{{ $leaveRequest->user?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NIM</td>
                    <td>{{ $leaveRequest->user?->intern?->nim ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Pengajuan</td>
                    <td>{{ $leaveRequest->typeLabel() }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Izin</td>
                    <td>
                        {{ $leaveRequest->start_date->translatedFormat('d M Y') }}
                        – {{ $leaveRequest->end_date->translatedFormat('d M Y') }}
                        ({{ $leaveRequest->total_days }} HK)
                    </td>
                </tr>
                <tr>
                    <td class="label">Alasan</td>
                    <td>{{ $leaveRequest->reason }}</td>
                </tr>
                <tr>
                    <td class="label">Status Akhir</td>
                    <td>
                        @if($leaveRequest->status === 'approved')
                            <span class="badge badge-approved">Disetujui</span>
                        @else
                            <span class="badge badge-rejected">Ditolak</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Diproses oleh</td>
                    <td>{{ $leaveRequest->approver?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Diproses</td>
                    <td>{{ $leaveRequest->approved_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                </tr>
                @if($leaveRequest->admin_note)
                <tr>
                    <td class="label">Catatan Admin</td>
                    <td>{{ $leaveRequest->admin_note }}</td>
                </tr>
                @endif
            </table>

            <p>Email ini dikirim otomatis sebagai informasi.</p>
        </div>
        <div class="footer">
            Sistem Absensi Peserta Magang &mdash; PTPN Holding
        </div>
    </div>
</body>
</html>
