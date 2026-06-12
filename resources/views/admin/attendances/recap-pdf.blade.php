<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #111; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 12px; }
        .company { font-size: 13px; font-weight: bold; }
        .title { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-top: 6px; }
        .meta { margin-bottom: 10px; }
        .meta td { padding: 1px 4px; }
        .meta td.label { width: 130px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #555; padding: 4px 5px; }
        table.data th { background: #eee; text-align: center; font-size: 9px; }
        table.data td { font-size: 9px; }
        td.num { text-align: right; }
        .empty { margin-top: 14px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PERKEBUNAN NUSANTARA</div>
        <div class="title">Rekap Absensi Peserta Magang</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Periode</td>
            <td>: {{ $recap['start_date']->translatedFormat('d F Y') }} &ndash; {{ $recap['end_date']->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Program</td>
            <td>: {{ $programName ?? 'Semua Program' }}</td>
        </tr>
        <tr>
            <td class="label">Hari Kerja Efektif</td>
            <td>: {{ $recap['effective_working_days'] }}</td>
        </tr>
    </table>

    @if (empty($recap['rows']))
        <p class="empty">Belum ada data peserta magang.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Program</th>
                    <th>Hari Kerja Efektif</th>
                    <th>WFO</th>
                    <th>WFH</th>
                    <th>Dinas</th>
                    <th>Izin</th>
                    <th>Tidak Absen</th>
                    <th>Terlambat Datang</th>
                    <th>Tidak CO</th>
                    <th>Persentase Terlambat Datang</th>
                    <th>Persentase Tidak Absen</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recap['rows'] as $row)
                    <tr>
                        <td>{{ $row['intern']->user->name }}</td>
                        <td>{{ $row['intern']->nim }}</td>
                        <td>{{ $row['intern']->internProgram?->name }}</td>
                        <td class="num">{{ $row['effective_working_days'] }}</td>
                        <td class="num">{{ $row['wfo'] }}</td>
                        <td class="num">{{ $row['wfh'] }}</td>
                        <td class="num">{{ $row['dinas'] }}</td>
                        <td class="num">{{ $row['izin'] }}</td>
                        <td class="num">{{ $row['tidak_absen'] }}</td>
                        <td class="num">{{ $row['terlambat'] }}</td>
                        <td class="num">{{ $row['tidak_co'] }}</td>
                        <td class="num">{{ number_format($row['persen_terlambat'], 1) }}%</td>
                        <td class="num">{{ number_format($row['persen_tidak_absen'], 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
