<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pengajuan {{ $leaveRequest->request_number }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 18px; }
        .company { font-size: 14px; font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; text-decoration: underline; margin-top: 14px; text-transform: uppercase; }
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
        .signatures { width: 100%; margin-top: 48px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 12px; }
        .sign-name { font-weight: bold; margin-top: 60px; text-decoration: underline; }
        .sign-role { font-size: 11px; }
        .status-box { margin-top: 18px; padding: 8px 10px; border: 1px solid #999; }
    </style>
</head>
<body>
    @php
        $intern = $leaveRequest->user?->intern;
    @endphp

    <div class="header">
        <div class="company">PERKEBUNAN NUSANTARA</div>
        <div class="title">Permohonan Izin / Sakit Peserta Magang</div>
        <div class="number">Nomor : {{ $leaveRequest->request_number }}</div>
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
            <td>
                Menyetujui,
                <div class="sign-name">{{ $leaveRequest->approver?->name ?? '-' }}</div>
                <div class="sign-role">Administrator</div>
            </td>
            <td>
                Jakarta, {{ $leaveRequest->created_at->format('d M Y') }}<br>
                Pemohon,
                <div class="sign-name">{{ $leaveRequest->user?->name ?? '-' }}</div>
                <div class="sign-role">Peserta Magang</div>
            </td>
        </tr>
    </table>
</body>
</html>
