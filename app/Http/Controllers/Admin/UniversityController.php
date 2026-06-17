<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUniversityRequest;
use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UniversityController extends Controller
{
    /**
     * Display a search-based listing of universities.
     *
     * The dataset contains thousands of records, so the list is never loaded
     * in full: results are filtered by a search term and paginated. An empty
     * search still paginates (ordered by name) but the UI is search-first.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $universities = University::query()
            ->withCount(['interns', 'studyPrograms'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Universities/Index', [
            'universities' => $universities,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new university.
     */
    public function create(): Response
    {
        return Inertia::render('admin/Universities/Create');
    }

    /**
     * Store a newly created university in storage.
     */
    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        University::create($request->validated());

        return redirect()
            ->route('admin.universities.index')
            ->with('status', 'Perguruan tinggi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified university.
     */
    public function edit(University $university): Response
    {
        return Inertia::render('admin/Universities/Edit', [
            'university' => $university,
        ]);
    }

    /**
     * Update the specified university in storage.
     */
    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $university->update($request->validated());

        return redirect()
            ->route('admin.universities.index')
            ->with('status', 'Perguruan tinggi berhasil diperbarui.');
    }

    /**
     * Remove the specified university from storage.
     *
     * A university that is still referenced by interns or study programs is
     * never hard-deleted so historical data stays intact; it should be
     * deactivated instead.
     */
    public function destroy(University $university): RedirectResponse
    {
        if ($university->interns()->exists() || $university->studyPrograms()->exists()) {
            return redirect()
                ->route('admin.universities.index')
                ->with('error', 'Perguruan tinggi tidak dapat dihapus karena masih digunakan. Nonaktifkan saja agar data lama tetap aman.');
        }

        $university->delete();

        return redirect()
            ->route('admin.universities.index')
            ->with('status', 'Perguruan tinggi berhasil dihapus.');
    }
}
