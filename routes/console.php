<?php

use App\Models\Intern;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Keep the stored internship status consistent with the calendar: once an
 * active intern's end date has passed, mark the internship completed. This
 * keeps status-based reporting and access control aligned with the dates even
 * if no one updates the record manually. Deactivated interns are left as-is.
 */
Schedule::call(function (): void {
    Intern::query()
        ->where('status', Intern::STATUS_ACTIVE)
        ->whereNotNull('end_date')
        ->whereDate('end_date', '<', now()->toDateString())
        ->update(['status' => Intern::STATUS_COMPLETED]);
})->dailyAt('00:05')->name('complete-ended-interns')->withoutOverlapping();

