<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Spreadsheet export for the attendance recap.
 *
 * This class only formats data already produced by AttendanceRecapService;
 * it performs no recap calculations of its own.
 */
class AttendanceRecapExport implements FromArray, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    /**
     * @param  array{
     *     start_date: Carbon,
     *     end_date: Carbon,
     *     effective_working_days: int,
     *     rows: array<int, array<string, mixed>>
     * }  $recap
     */
    public function __construct(
        private readonly array $recap,
        private readonly ?string $programName = null,
    ) {
    }

    /**
     * Build the full sheet layout (title, period, headings, data rows).
     *
     * @return array<int, array<int, string|int|float|null>>
     */
    public function array(): array
    {
        $period = $this->recap['start_date']->translatedFormat('d M Y')
            .' - '.$this->recap['end_date']->translatedFormat('d M Y');

        $rows = [
            ['Rekap Absensi'],
            ['Periode', $period],
            ['Hari Kerja Efektif', $this->recap['effective_working_days']],
            ['Program', $this->programName ?? 'Semua Program'],
            [],
            [
                'Nama',
                'NIM',
                'Program',
                'Hari Kerja Efektif',
                'WFO',
                'WFH',
                'Dinas',
                'Izin',
                'Tidak Absen',
                'Terlambat Datang',
                'Tidak CO',
                'Persentase Terlambat Datang',
                'Persentase Tidak Absen',
            ],
        ];

        foreach ($this->recap['rows'] as $row) {
            $rows[] = [
                $row['intern']->user->name,
                $row['intern']->nim,
                $row['intern']->internProgram?->name,
                $row['effective_working_days'],
                $row['wfo'],
                $row['wfh'],
                $row['dinas'],
                $row['izin'],
                $row['tidak_absen'],
                $row['terlambat'],
                $row['tidak_co'],
                number_format($row['persen_terlambat'], 1).'%',
                number_format($row['persen_tidak_absen'], 1).'%',
            ];
        }

        return $rows;
    }

    /**
     * The worksheet tab title.
     */
    public function title(): string
    {
        return 'Rekap Absensi';
    }

    /**
     * Apply basic styling to the title and heading rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            6 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Register sheet events for light table formatting.
     *
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 6 + count($this->recap['rows']);

                // Borders around the data table (header + rows).
                $sheet->getStyle('A6:M'.$lastRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}
