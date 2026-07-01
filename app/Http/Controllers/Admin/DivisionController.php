<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DivisionController extends Controller
{
    /**
     * Display a listing of the divisions.
     */
    public function index(): Response
    {
        $divisions = Division::query()
            ->withCount('interns')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Divisions/Index', [
            'divisions' => $divisions,
        ]);
    }

    /**
     * Show the form for creating a new division.
     */
    public function create(): Response
    {
        return Inertia::render('admin/Divisions/Create');
    }

    /**
     * Store a newly created division in storage.
     */
    public function store(StoreDivisionRequest $request): RedirectResponse
    {
        Division::create($request->validated());

        return redirect()
            ->route('admin.divisions.index')
            ->with('status', 'Divisi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified division.
     */
    public function edit(Division $division): Response
    {
        return Inertia::render('admin/Divisions/Edit', [
            'division' => $division,
        ]);
    }

    /**
     * Update the specified division in storage.
     */
    public function update(UpdateDivisionRequest $request, Division $division): RedirectResponse
    {
        $division->update($request->validated());

        return redirect()
            ->route('admin.divisions.index')
            ->with('status', 'Divisi berhasil diperbarui.');
    }

    /**
     * Remove the specified division from storage.
     *
     * Divisions that still have interns assigned cannot be deleted so
     * historical data stays intact; deactivate them instead.
     */
    public function destroy(Division $division): RedirectResponse
    {
        if ($division->interns()->exists()) {
            return redirect()
                ->route('admin.divisions.index')
                ->with('error', 'Divisi tidak dapat dihapus karena masih memiliki peserta. Nonaktifkan divisi jika tidak digunakan lagi.');
        }

        $division->delete();

        return redirect()
            ->route('admin.divisions.index')
            ->with('status', 'Divisi berhasil dihapus.');
    }
}
