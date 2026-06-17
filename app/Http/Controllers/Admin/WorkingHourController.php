<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWorkingHourRequest;
use App\Models\WorkingHour;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkingHourController extends Controller
{
    /**
     * Display the working hours for every day of the week.
     */
    public function index(): Response
    {
        // Guarantee a row exists for each day so the table is always complete.
        WorkingHour::ensureSeeded();

        $workingHours = WorkingHour::orderBy('day_of_week')->get();

        return Inertia::render('admin/WorkingHours/Index', [
            'workingHours' => $workingHours,
        ]);
    }

    /**
     * Show the form for editing a single day's working hours.
     */
    public function edit(WorkingHour $workingHour): Response
    {
        return Inertia::render('admin/WorkingHours/Edit', [
            'workingHour' => $workingHour,
        ]);
    }

    /**
     * Update the working hours for a single day.
     */
    public function update(UpdateWorkingHourRequest $request, WorkingHour $workingHour): RedirectResponse
    {
        $workingHour->update($request->validated());

        return redirect()
            ->route('admin.working-hours.index')
            ->with('status', 'Jam kerja berhasil diperbarui.');
    }
}
