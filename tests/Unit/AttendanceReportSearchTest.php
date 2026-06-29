<?php

namespace Tests\Unit;

use App\Services\AttendanceReportService;
use PHPUnit\Framework\TestCase;

class AttendanceReportSearchTest extends TestCase
{
    private function row(array $overrides = []): array
    {
        return array_merge([
            'nim' => '123', 'nama' => 'Budi', 'program' => 'IT', 'divisi' => 'TI',
            'hari' => 'Senin', 'jenis_absen' => 'WFO', 'check_in' => '08:00',
            'check_out' => '17:00', 'category' => 'wfo', 'is_late' => false,
            'hari_kerja' => true,
        ], $overrides);
    }

    public function test_apply_search_keeps_matching_rows_only(): void
    {
        $report = [
            'rows' => [
                $this->row(['nama' => 'Budi']),
                $this->row(['nama' => 'Siti', 'divisi' => 'HRD']),
            ],
            'summary' => [],
            'chart' => [],
        ];

        $result = (new AttendanceReportService)->applySearch($report, 'budi');

        $this->assertCount(1, $result['rows']);
        $this->assertSame('Budi', $result['rows'][0]['nama']);
    }

    public function test_apply_search_returns_all_rows_when_blank(): void
    {
        $report = ['rows' => [$this->row(), $this->row()], 'summary' => [], 'chart' => []];

        $result = (new AttendanceReportService)->applySearch($report, '  ');

        $this->assertCount(2, $result['rows']);
    }
}
