<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Dokumen &mdash; {{ $leaveRequest->request_number }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; color: #111827; margin: 0; padding: 40px 16px; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.12); padding: 32px; }
        .brand { font-weight: 700; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 24px; }
        .badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 999px; font-size: 15px; font-weight: 600; margin-bottom: 28px; }
        .badge-valid   { background: #d1fae5; color: #065f46; }
        .badge-invalid { background: #fee2e2; color: #991b1b; }
        .badge-icon { font-size: 18px; }
        dl { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 0; }
        dt { font-size: 12px; color: #6b7280; padding-top: 10px; }
        dd { font-size: 14px; color: #111827; font-weight: 500; padding-top: 10px; padding-left: 4px; border-bottom: 1px solid #f3f4f6; }
        .footer { margin-top: 28px; font-size: 11px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">Perkebunan Nusantara &mdash; Sistem Magang</div>
        <h1>Verifikasi Dokumen Pengajuan</h1>

        @if ($isValid)
            <div class="badge badge-valid">
                <span class="badge-icon">&#10003;</span> Dokumen Valid
            </div>
        @else
            <div class="badge badge-invalid">
                <span class="badge-icon">&#10007;</span> Dokumen Tidak Valid
            </div>
        @endif

        <dl>
            <dt>Nomor Pengajuan</dt>
            <dd>{{ $leaveRequest->request_number }}</dd>

            <dt>Nama Peserta Magang</dt>
            <dd>{{ $leaveRequest->user?->name ?? '—' }}</dd>

            <dt>Jenis Pengajuan</dt>
            <dd>{{ $leaveRequest->typeLabel() }}</dd>

            <dt>Status Persetujuan</dt>
            <dd>{{ $leaveRequest->statusLabel() }}</dd>

            <dt>Disetujui Oleh</dt>
            <dd>{{ $leaveRequest->approver?->name ?? '—' }}</dd>

            <dt>Tanggal Persetujuan</dt>
            <dd>{{ $leaveRequest->approved_at?->format('d M Y, H:i') ?? '—' }}</dd>
        </dl>

        <p class="footer">
            Halaman ini dibuat otomatis oleh sistem. Verifikasi dilakukan berdasarkan data yang tersimpan.
        </p>
    </div>
</body>
</html>
