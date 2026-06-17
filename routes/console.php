<?php

use App\Models\Intern;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Keep the stored internship status aligned with the calendar so that simple
 * status-based reporting and filtering stay accurate without anyone editing
 * records by hand. The effective status (upcoming / active / completed) is
 * always derived from the dates at read time; this schedule only writes the
 * derived value back to the column. Manually deactivated interns are skipped
 * so the administrator's override is preserved. Completed interns are retained
 * in the database for historical attendance, leave requests and audit.
 */
Schedule::call(function (): void {
    $today = now()->startOfDay();

    Intern::query()
        ->where('status', '!=', Intern::STATUS_INACTIVE)
        ->whereNotNull('end_date')
        ->whereDate('end_date', '<', $today)
        ->where('status', '!=', Intern::STATUS_COMPLETED)
        ->update(['status' => Intern::STATUS_COMPLETED]);

    Intern::query()
        ->where('status', '!=', Intern::STATUS_INACTIVE)
        ->where(function ($q) use ($today): void {
            $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
        })
        ->where(function ($q) use ($today): void {
            $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
        })
        ->where('status', '!=', Intern::STATUS_ACTIVE)
        ->update(['status' => Intern::STATUS_ACTIVE]);

    Intern::query()
        ->where('status', '!=', Intern::STATUS_INACTIVE)
        ->whereNotNull('start_date')
        ->whereDate('start_date', '>', $today)
        ->where('status', '!=', Intern::STATUS_UPCOMING)
        ->update(['status' => Intern::STATUS_UPCOMING]);
})->dailyAt('00:05')->name('sync-intern-statuses')->withoutOverlapping();

