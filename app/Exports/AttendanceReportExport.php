<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Spreadsheet export for the attendance report.
 *
 * Matches the company reference format exactly:
 *  - Row 1 = column headers (no title/meta block above)
 *  - Header fill #4285F4 (Calibri 11 bold white, bottom-aligned, no wrap)
 *  - Plain white data rows (Calibri 11, no bold)
 *  - No cell borders
 *  - Freeze row 1 (A2), no auto-filter
 *  - Auto-sized columns; col A (No) fixed at width 4
 */
class AttendanceReportExport implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    /** ARGB colour taken directly from the company reference file. */
    private const HEADER_FILL = 'FF4285F4';

    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(
        private readonly array $report,
    ) {
    }

    /**
     * Build the sheet: one header row followed by data rows.
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function array(): array
    {
        $rows = [
            [
                'No', 'NIM', 'Nama', 'Tanggal',
                'Program Magang', 'Divisi', 'Hari', 'Hari Kerja',
                'Jenis Absen', 'Check In Skedul', 'Check In',
                'Check In Lat', 'Check In Long',
                'Check Out Skedul', 'Check Out',
                'Check Out Lat', 'Check Out Long',
                'Jam Bekerja', 'Jarak',
                'Status Kedatangan', 'Status Kepulangan',
                'Keterlambatan', 'Mood Masuk', 'Mood Pulang',
            ],
        ];

        $no = 1;

        foreach ($this->report['rows'] as $row) {
            $rows[] = [
                $no++,
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
                $row['check_in_lat'] !== null ? (string) $row['check_in_lat'] : null,
                $row['check_in_long'] !== null ? (string) $row['check_in_long'] : null,
                $row['check_out_schedule'],
                $row['check_out'],
                $row['check_out_lat'] !== null ? (string) $row['check_out_lat'] : null,
                $row['check_out_long'] !== null ? (string) $row['check_out_long'] : null,
                $row['jam_bekerja'],
                $row['jarak'],
                $row['status_kedatangan'] ?: null,
                $row['status_kepulangan'] ?: null,
                $row['keterlambatan'],
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
        return 'Data Lengkap';
    }

    /**
     * Apply styling that exactly mirrors the company reference file.
     *
     * @return array<string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $ws = $event->sheet->getDelegate();
                $lastCol = 'X'; // 24 columns (A–X)
                $lastRow = 1 + count($this->report['rows']);

                // ── Header row (row 1) ────────────────────────────────────
                $ws->getStyle('A1:'.$lastCol.'1')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => self::HEADER_FILL],
                    ],
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 11,
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_GENERAL,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
                        'wrapText' => false,
                    ],
                ]);

                // ── Data rows ─────────────────────────────────────────────
                if ($lastRow > 1) {
                    $ws->getStyle('A2:'.$lastCol.$lastRow)->applyFromArray([
                        'font' => [
                            'name' => 'Calibri',
                            'size' => 11,
                            'bold' => false,
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
                        ],
                    ]);
                }

                // ── Col A fixed width (No column) ─────────────────────────
                $ws->getColumnDimension('A')->setAutoSize(false)->setWidth(4);

                // ── Freeze header row ─────────────────────────────────────
                $ws->freezePane('A2');
            },
        ];
    }
}
