<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Spreadsheet export for the daily attendance report.
 *
 * This class only formats data already produced by AttendanceReportService;
 * it performs no calculations of its own.
 */
class AttendanceReportExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(
        private readonly array $report,
        private readonly ?string $programName = null,
        private readonly ?string $divisionName = null,
    ) {
    }

    /**
     * Build the full sheet layout (title, meta, headings, data rows).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function array(): array
    {
        $date = Carbon::parse($this->report['date'])->translatedFormat('d M Y');
        $summary = $this->report['summary'];

        $rows = [
            ['Reporting Absensi'],
            ['Tanggal', $date.' ('.$this->report['day_label'].')'],
            ['Hari Kerja', $this->report['is_working_day'] ? 'Ya' : 'Tidak'],
            ['Program Magang', $this->programName ?? 'Semua Program'],
            ['Divisi', $this->divisionName ?? 'Semua Divisi'],
            [
                'Total Peserta', $summary['total_peserta'],
                'Total Hadir', $summary['total_hadir'],
                'Terlambat', $summary['terlambat'],
                'Izin', $summary['izin'],
                'Tidak Hadir', $summary['tidak_hadir'],
            ],
            [],
            [
                'NIM',
                'Nama',
                'Tanggal',
                'Program Magang',
                'Divisi',
                'Hari',
                'Hari Kerja',
                'Jenis Absen',
                'Check In Skedul',
                'Check In',
                'Check In Lat',
                'Check In Long',
                'Check Out Skedul',
                'Check Out',
                'Check Out Lat',
                'Check Out Long',
                'Mood Masuk',
                'Mood Pulang',
            ],
        ];

        foreach ($this->report['rows'] as $row) {
            $rows[] = [
                $row['nim'],
                $row['nama'],
                $row['tanggal'],
                $row['program'],
                $row['divisi'],
                $row['hari'],
                $row['hari_kerja'] ? 'Ya' : 'Tidak',
                $row['jenis_absen'],
                $row['check_in_schedule'],
                $row['check_in'],
                $row['check_in_lat'],
                $row['check_in_long'],
                $row['check_out_schedule'],
                $row['check_out'],
                $row['check_out_lat'],
                $row['check_out_long'],
                $row['mood_in'],
                $row['mood_out'],
            ];
        }

        return $rows;
    }

    /**
     * The worksheet tab title.
     */
    public function title(): string
    {
        return 'Reporting Absensi';
    }

    /**
     * Apply basic styling to the title and heading rows.
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            8 => ['font' => ['bold' => true]],
        ];
    }
}
