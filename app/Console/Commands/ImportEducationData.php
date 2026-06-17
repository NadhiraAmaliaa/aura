<?php

namespace App\Console\Commands;

use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEducationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import-education
        {--universities=database/data/perguruan-tinggi.csv : Path to the universities CSV}
        {--study-programs=database/data/program-studi.csv : Path to the study programs CSV}
        {--fresh : Remove existing universities and study programs before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the universities and study programs master data from the provided CSV datasets.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $universitiesPath = base_path((string) $this->option('universities'));
        $studyProgramsPath = base_path((string) $this->option('study-programs'));

        if (! is_file($universitiesPath)) {
            $this->error("Universities CSV not found: {$universitiesPath}");

            return self::FAILURE;
        }

        if (! is_file($studyProgramsPath)) {
            $this->error("Study programs CSV not found: {$studyProgramsPath}");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Removing existing master data (study programs, universities)...');
            // Order matters: study programs reference universities.
            StudyProgram::query()->delete();
            University::query()->delete();
        }

        $this->importUniversities($universitiesPath);
        $this->importStudyPrograms($studyProgramsPath);

        $this->info('Education master data imported successfully.');

        return self::SUCCESS;
    }

    /**
     * Import universities from the CSV file.
     *
     * Columns: No, Nama, LLDikti.
     */
    protected function importUniversities(string $path): void
    {
        $this->line('Importing universities...');

        $existing = University::query()
            ->pluck('id', 'name')
            ->all();

        $batch = [];
        $imported = 0;
        $now = now();

        foreach ($this->readCsv($path) as $row) {
            $name = $this->clean($row[1] ?? '');

            if ($name === '' || isset($existing[$name])) {
                continue;
            }

            // Guard against duplicate names within the same file.
            $existing[$name] = true;

            $batch[] = [
                'name' => $name,
                'lldikti' => $this->clean($row[2] ?? '') ?: null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // 5 columns per row; keep each statement well under SQL Server's
            // 2100-parameter limit.
            if (count($batch) >= $this->maxRowsPerInsert(5)) {
                DB::table('universities')->insert($batch);
                $imported += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('universities')->insert($batch);
            $imported += count($batch);
        }

        $this->info("  Universities imported: {$imported}");
    }

    /**
     * Import study programs from the CSV file, linking each to its university
     * by name when a matching university exists.
     *
     * Columns: No, Nama Prodi, Nama PT, Jenjang, LLDikti.
     */
    protected function importStudyPrograms(string $path): void
    {
        $this->line('Importing study programs...');

        // Map university name => id for linking, loaded once.
        $universityIds = University::query()
            ->pluck('id', 'name')
            ->all();

        // Track existing (university_id, name, level) tuples to avoid duplicates.
        // The level is part of the key because a university may offer the same
        // program name at different levels (e.g. D-III and S1).
        $existing = StudyProgram::query()
            ->select(['university_id', 'name', 'level'])
            ->get()
            ->map(fn ($program): string => $this->pairKey($program->university_id, $program->name, $program->level))
            ->flip()
            ->all();

        $batch = [];
        $imported = 0;
        $now = now();

        foreach ($this->readCsv($path) as $row) {
            $name = $this->clean($row[1] ?? '');
            $universityName = $this->clean($row[2] ?? '');

            if ($name === '') {
                continue;
            }

            $universityId = $universityName !== '' ? ($universityIds[$universityName] ?? null) : null;
            $level = $this->clean($row[3] ?? '') ?: null;
            $key = $this->pairKey($universityId, $name, $level);

            if (isset($existing[$key])) {
                continue;
            }

            $existing[$key] = true;

            $batch[] = [
                'university_id' => $universityId,
                'name' => $name,
                'level' => $level,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // 6 columns per row; keep each statement well under SQL Server's
            // 2100-parameter limit.
            if (count($batch) >= $this->maxRowsPerInsert(6)) {
                DB::table('study_programs')->insert($batch);
                $imported += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('study_programs')->insert($batch);
            $imported += count($batch);
        }

        $this->info("  Study programs imported: {$imported}");
    }

    /**
     * Read a CSV file row by row as a generator to keep memory usage low.
     *
     * The first (header) row is skipped.
     *
     * @return \Generator<int, array<int, string>>
     */
    protected function readCsv(string $path): \Generator
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return;
        }

        try {
            $isHeader = true;

            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                if ($isHeader) {
                    $isHeader = false;

                    continue;
                }

                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Normalise a CSV cell: strip a UTF-8 BOM, trim surrounding whitespace and
     * collapse common HTML entities found in the source data.
     */
    protected function clean(string $value): string
    {
        $value = str_replace("\xEF\xBB\xBF", '', $value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($value);
    }

    /**
     * Build a stable key for a (university_id, name, level) tuple.
     */
    protected function pairKey(?int $universityId, string $name, ?string $level = null): string
    {
        return ($universityId ?? 0).'|'.mb_strtolower($name).'|'.mb_strtolower((string) $level);
    }

    /**
     * The maximum number of rows that can be inserted in a single statement
     * for a table with the given number of bound columns per row.
     *
     * SQL Server allows at most 2100 bound parameters per statement, so the
     * batch size must scale down as the column count grows. The result is also
     * capped so that very large multi-row INSERT statements do not exhaust the
     * SQL Server query memory pool on constrained editions.
     */
    protected function maxRowsPerInsert(int $columnsPerRow): int
    {
        $maxParameters = 2000;
        $maxRows = 100;

        $byParameters = (int) floor($maxParameters / max(1, $columnsPerRow));

        return max(1, min($maxRows, $byParameters));
    }
}
