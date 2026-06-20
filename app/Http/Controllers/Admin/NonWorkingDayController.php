<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNonWorkingDayRequest;
use App\Http\Requests\UpdateNonWorkingDayRequest;
use App\Models\NonWorkingDay;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NonWorkingDayController extends Controller
{
    /**
     * Display a listing of the non-working days.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->integer('perPage', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $nonWorkingDays = NonWorkingDay::orderByDesc('date')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('admin/NonWorkingDays/Index', [
            'nonWorkingDays' => $nonWorkingDays,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new non-working day.
     */
    public function create(): Response
    {
        return Inertia::render('admin/NonWorkingDays/Create');
    }

    /**
     * Store a newly created non-working day in storage.
     */
    public function store(StoreNonWorkingDayRequest $request): RedirectResponse
    {
        NonWorkingDay::create($request->validated());

        return redirect()
            ->route('admin.non-working-days.index')
            ->with('status', 'Hari libur berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified non-working day.
     */
    public function edit(NonWorkingDay $nonWorkingDay): Response
    {
        return Inertia::render('admin/NonWorkingDays/Edit', [
            'nonWorkingDay' => $nonWorkingDay,
        ]);
    }

    /**
     * Update the specified non-working day in storage.
     */
    public function update(UpdateNonWorkingDayRequest $request, NonWorkingDay $nonWorkingDay): RedirectResponse
    {
        $nonWorkingDay->update($request->validated());

        return redirect()
            ->route('admin.non-working-days.index')
            ->with('status', 'Hari libur berhasil diperbarui.');
    }

    /**
     * Remove the specified non-working day from storage.
     */
    public function destroy(NonWorkingDay $nonWorkingDay): RedirectResponse
    {
        $nonWorkingDay->delete();

        return redirect()
            ->route('admin.non-working-days.index')
            ->with('status', 'Hari libur berhasil dihapus.');
    }
}
