<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInternProgramRequest;
use App\Http\Requests\UpdateInternProgramRequest;
use App\Models\InternProgram;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InternProgramController extends Controller
{
    /**
     * Display a listing of the intern programs.
     */
    public function index(): Response
    {
        $programs = InternProgram::query()
            ->withCount('interns')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/InternPrograms/Index', [
            'programs' => $programs,
        ]);
    }

    /**
     * Show the form for creating a new intern program.
     */
    public function create(): Response
    {
        return Inertia::render('admin/InternPrograms/Create');
    }

    /**
     * Store a newly created intern program in storage.
     */
    public function store(StoreInternProgramRequest $request): RedirectResponse
    {
        InternProgram::create($request->validated());

        return redirect()
            ->route('admin.intern-programs.index')
            ->with('status', 'Program magang berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified intern program.
     */
    public function edit(InternProgram $internProgram): Response
    {
        return Inertia::render('admin/InternPrograms/Edit', [
            'program' => $internProgram,
        ]);
    }

    /**
     * Update the specified intern program in storage.
     */
    public function update(UpdateInternProgramRequest $request, InternProgram $internProgram): RedirectResponse
    {
        $internProgram->update($request->validated());

        return redirect()
            ->route('admin.intern-programs.index')
            ->with('status', 'Program magang berhasil diperbarui.');
    }

    /**
     * Remove the specified intern program from storage.
     *
     * Programs that still have interns assigned cannot be deleted so historical
     * data stays intact.
     */
    public function destroy(InternProgram $internProgram): RedirectResponse
    {
        if ($internProgram->interns()->exists()) {
            return redirect()
                ->route('admin.intern-programs.index')
                ->with('error', 'Program magang tidak dapat dihapus karena masih memiliki peserta.');
        }

        $internProgram->delete();

        return redirect()
            ->route('admin.intern-programs.index')
            ->with('status', 'Program magang berhasil dihapus.');
    }
}
