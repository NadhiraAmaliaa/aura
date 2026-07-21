<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Izin Pulang Sebelum Waktunya</title>
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
        .title { font-size: 13px; font-weight: bold; text-decoration: underline; margin-top: 6px; text-transform: uppercase; }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td { padding: 2px 4px; vertical-align: top; }
        table.info td.label { width: 34%; }
        table.info td.colon { width: 12px; }
        .section { margin-top: 16px; }
        .bold { font-weight: bold; }
        .body-text { margin-top: 16px; text-align: justify; line-height: 1.5; }
        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 12px; padding: 0 8px; }
        .sign-date { text-align: right; padding-right: 8px; font-size: 12px; padding-bottom: 20px; }
        .sign-title { margin-bottom: 8px; text-align: center; }
        .sign-space { height: 72px; }
        .sign-name { font-weight: bold; text-decoration: underline; }
        .sign-role { font-size: 11px; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                {{-- Left: Danantara logo --}}
                <td class="header-logo-left">
                    @if($logos['danantara'])
                        <img src="{{ $logos['danantara'] }}" alt="Danantara Indonesia">
                    @endif
                </td>

                {{-- Center: document title --}}
                <td class="header-center">
                    <div class="title">Surat Izin Pulang Sebelum Waktunya</div>
                </td>

                {{-- Right: Holding + PTPN logos --}}
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

    <p style="margin: 0 0 4px 0;">Yang bertanda tangan di bawah ini:</p>

    <table class="info">
        <tr>
            <td class="label">Nama</td><td class="colon">:</td>
            <td>{{ $identity['name'] }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td><td class="colon">:</td>
            <td>{{ $identity['nim'] }}</td>
        </tr>
        <tr>
            <td class="label">Program Magang</td><td class="colon">:</td>
            <td>{{ $identity['program'] }}</td>
        </tr>
        <tr>
            <td class="label">Universitas</td><td class="colon">:</td>
            <td>{{ $identity['university'] }}</td>
        </tr>
        <tr>
            <td class="label">Jurusan</td><td class="colon">:</td>
            <td>{{ $identity['major'] }}</td>
        </tr>
        <tr>
            <td class="label">Divisi</td><td class="colon">:</td>
            <td>{{ $identity['division'] }}</td>
        </tr>
    </table>

    <div class="section">
        <table class="info">
            <tr>
                <td class="label">Tanggal Pulang</td><td class="colon">:</td>
                <td>{{ $earlyLeaveDate->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Jam Meninggalkan Kantor</td><td class="colon">:</td>
                <td>{{ $leaveTime }} WIB</td>
            </tr>
            <tr>
                <td class="label">Alasan</td><td class="colon">:</td>
                <td>{{ $reason }}</td>
            </tr>
        </table>
    </div>

    <div class="body-text">
        Dengan ini mengajukan permohonan izin untuk meninggalkan kantor sebelum waktu kerja
        berakhir pada tanggal {{ $earlyLeaveDate->format('d M Y') }} pukul {{ $leaveTime }} WIB
        dengan alasan sebagaimana tersebut di atas. Saya bertanggung jawab penuh atas izin ini dan
        akan menyelesaikan kewajiban saya sesuai ketentuan yang berlaku. Demikian surat permohonan
        ini saya buat dengan sebenar-benarnya. Atas perhatian dan izin yang diberikan, saya ucapkan
        terima kasih.
    </div>

    <table class="signatures">
        <tr>
            <td></td>
            <td class="sign-date">{{ $city }}, {{ $createdDate->format('d M Y') }}</td>
        </tr>
        <tr>
            <td>
                <div class="sign-title">Mengetahui,</div>
                <div class="sign-space"></div>
                <div class="sign-name">(&nbsp;.......................................&nbsp;)</div>
                <div class="sign-role">Petugas / SDM</div>
            </td>
            <td>
                <div class="sign-title">Hormat saya,</div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $identity['name'] }}</div>
                <div class="sign-role">Peserta Magang</div>
            </td>
        </tr>
    </table>
</body>
</html>
