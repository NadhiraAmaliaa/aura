<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pengajuan {{ $leaveRequest->request_number }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111; margin: 0; }
        .header { border-bottom: 2px solid #111; padding-bottom: 16px; margin-bottom: 24px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo-left { width: 18%; vertical-align: middle; }
        .header-logo-left img { max-height: 50px; max-width: 100%; display: block; }
        .header-center { vertical-align: middle; text-align: center; }
        .header-logo-right { width: 22%; vertical-align: middle; text-align: right; }
        .header-logo-right img { max-height: 50px; max-width: 100%; display: inline-block; margin: 0 4px 0 0; }
        .header-logo-holding { max-height: 40px !important; margin: 0 4px 0 0 !important; }
        .header-logo-right img:last-child { margin: 0 0 0 0; }
        .header-logo-right-inner { display: inline-block; text-align: center; }
        .company { font-size: 14px; font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; text-decoration: underline; margin-top: 6px; text-transform: uppercase; }
        .number { font-weight: bold; margin-top: 4px; }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td { padding: 2px 4px; vertical-align: top; }
        table.info td.label { width: 34%; }
        table.info td.colon { width: 12px; }
        .section { margin-top: 16px; }
        .bold { font-weight: bold; }
        .row { width: 100%; }
        .row td { padding: 2px 4px; }
        .row td.amount { text-align: left; width: 28%; }
        .reason { margin-top: 16px; }
        .signatures { width: 100%; margin-top: 48px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 12px; padding: 0 8px; }
        .sign-date { text-align: right; padding-right: 8px; font-size: 12px; padding-bottom: 20px; }
        .sign-title { margin-bottom: 8px; text-align: center; }
        .sign-name { font-weight: bold; margin-top: 8px; text-decoration: underline; }
        .sign-role { font-size: 11px; margin-top: 2px; }
        .status-box { margin-top: 18px; padding: 8px 10px; border: 1px solid #999; }
        .sign-qr { text-align: center; margin: 4px 0; }
        .sign-qr img { display: inline-block; }
    </style>
</head>
<body>
    @php
        $intern = $leaveRequest->user?->intern;
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                {{-- Left: Danantara logo --}}
                <td class="header-logo-left">
                    @if($logos['danantara'])
                        <img src="{{ $logos['danantara'] }}" alt="Danantara Indonesia">
                    @endif
                </td>

                {{-- Center: Company name, document title and request number --}}
                <td class="header-center">
                    <div class="title">Permohonan Izin / Sakit Peserta Magang</div>
                    <div class="number">Nomor : {{ $leaveRequest->request_number }}</div>
                </td>

                {{-- Right: Holding + PTPN logos stacked --}}
                <td class="header-logo-right">
                    @if($logos['holding'])
                        <img src="{{ $logos['holding'] }}" alt="Holding Perkebunan Nusantara" class="header-logo-holding">
                    @endif
                    @if($logos['ptpn'])
                        <img src="{{ $logos['ptpn'] }}" alt="PTPN">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="info">
        <tr>
            <td class="label">Nama</td><td class="colon">:</td>
            <td>{{ $leaveRequest->user?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td><td class="colon">:</td>
            <td>{{ $intern?->nim ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Program Magang</td><td class="colon">:</td>
            <td>{{ $intern?->internProgram?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Divisi</td><td class="colon">:</td>
            <td>{{ $intern?->division ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Pengajuan</td><td class="colon">:</td>
            <td>{{ $leaveRequest->typeLabel() }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pengajuan</td><td class="colon">:</td>
            <td>{{ $leaveRequest->created_at->format('d M Y') }}</td>
        </tr>
    </table>

    <div class="section">
        <table class="row">
            <tr>
                <td>Mohon menjalani izin pada tanggal</td>
                <td class="colon">:</td>
                <td class="amount">
                    {{ $leaveRequest->start_date->format('d M Y') }} s/d {{ $leaveRequest->end_date->format('d M Y') }}
                </td>
            </tr>
            <tr class="bold">
                <td>Lama Hari</td>
                <td class="colon">:</td>
                <td class="amount">{{ $leaveRequest->total_days }} HK</td>
            </tr>
        </table>
    </div>

    <div class="reason">
        <div class="bold">Keterangan / alasan :</div>
        <div>{{ $leaveRequest->reason }}</div>
    </div>

    <div class="reason">
        <div class="bold">Alamat dan nomor telepon selama izin :</div>
        <div>{{ $leaveRequest->address ?: '-' }}</div>
        <div>{{ $leaveRequest->contact_phone ?: '-' }}</div>
    </div>

    <div class="status-box">
        <table class="info">
            <tr>
                <td class="label bold">Status Persetujuan</td><td class="colon">:</td>
                <td class="bold">{{ $leaveRequest->statusLabel() }}</td>
            </tr>
            <tr>
                <td class="label">Disetujui oleh</td><td class="colon">:</td>
                <td>{{ $leaveRequest->approver?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Persetujuan</td><td class="colon">:</td>
                <td>{{ $leaveRequest->approved_at?->format('d M Y, H:i') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Catatan Admin</td><td class="colon">:</td>
                <td>{{ $leaveRequest->admin_note ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td></td>
            <td class="sign-date">Jakarta, {{ $leaveRequest->created_at->format('d M Y') }}</td>
        </tr>
        <tr>
            <td>
                <div class="sign-title">Menyetujui,</div>
                <div class="sign-qr">
                    <img src="data:image/svg+xml;base64,{{ $supervisorQr }}" width="80" height="80" alt="Tanda tangan supervisor">
                </div>
                <div class="sign-name">{{ $leaveRequest->approver?->name ?? '-' }}</div>
                <div class="sign-role">Administrator</div>
            </td>
            <td>
                <div class="sign-title">Pemohon,</div>
                <div class="sign-qr">
                    <img src="data:image/svg+xml;base64,{{ $internQr }}" width="80" height="80" alt="Tanda tangan peserta magang">
                </div>
                <div class="sign-name">{{ $leaveRequest->user?->name ?? '-' }}</div>
                <div class="sign-role">Peserta Magang</div>
            </td>
        </tr>
    </table>
</body>
</html>
