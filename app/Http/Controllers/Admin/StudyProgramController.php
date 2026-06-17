<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudyProgramRequest;
use App\Http\Requests\UpdateStudyProgramRequest;
use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudyProgramController extends Controller
{
    /**
     * Display a search/university-filtered listing of study programs.
     *
     * Study programs are managed under a university or via search, never as a
     * single page that loads thousands of records. The university filter is
     * driven by the searchable autocomplete, so only the selected university
     * (if any) is sent to the frontend for display.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $universityId = $request->filled('university_id')
            ? (int) $request->query('university_id')
            : null;

        $studyPrograms = StudyProgram::query()
            ->with('university:id,name')
            ->withCount('interns')
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($universityId !== null, fn ($query) => $query->where('university_id', $universityId))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $selectedUniversity = $universityId !== null
            ? University::query()->find($universityId, ['id', 'name'])
            : null;

        return Inertia::render('admin/StudyPrograms/Index', [
            'studyPrograms' => $studyPrograms,
            'selectedUniversity' => $selectedUniversity,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'university_id' => $universityId,
            ],
        ]);
    }

    /**
     * Show the form for creating a new study program.
     */
    public function create(Request $request): Response
    {
        $universityId = $request->filled('university_id')
            ? (int) $request->query('university_id')
            : null;

        $university = $universityId !== null
            ? University::query()->find($universityId, ['id', 'name'])
            : null;

        return Inertia::render('admin/StudyPrograms/Create', [
            'university' => $university,
        ]);
    }

    /**
     * Store a newly created study program in storage.
     */
    public function store(StoreStudyProgramRequest $request): RedirectResponse
    {
        StudyProgram::create($request->validated());

        return redirect()
            ->route('admin.study-programs.index', ['university_id' => $request->integer('university_id')])
            ->with('status', 'Program studi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified study program.
     */
    public function edit(StudyProgram $studyProgram): Response
    {
        $studyProgram->load('university:id,name');

        return Inertia::render('admin/StudyPrograms/Edit', [
            'studyProgram' => $studyProgram,
        ]);
    }

    /**
     * Update the specified study program in storage.
     */
    public function update(UpdateStudyProgramRequest $request, StudyProgram $studyProgram): RedirectResponse
    {
        $studyProgram->update($request->validated());

        return redirect()
            ->route('admin.study-programs.index', ['university_id' => $request->integer('university_id')])
            ->with('status', 'Program studi berhasil diperbarui.');
    }

    /**
     * Remove the specified study program from storage.
     *
     * A study program still referenced by interns is never hard-deleted so
     * historical data stays intact; it should be deactivated instead.
     */
    public function destroy(StudyProgram $studyProgram): RedirectResponse
    {
        if ($studyProgram->interns()->exists()) {
            return redirect()
                ->route('admin.study-programs.index')
                ->with('error', 'Program studi tidak dapat dihapus karena masih digunakan oleh peserta. Nonaktifkan saja agar data lama tetap aman.');
        }

        $studyProgram->delete();

        return redirect()
            ->route('admin.study-programs.index')
            ->with('status', 'Program studi berhasil dihapus.');
    }
}
