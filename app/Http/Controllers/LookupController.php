<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LookupController extends Controller
{
    /**
     * Maximum number of suggestions returned per lookup request.
     */
    private const LIMIT = 20;

    /**
     * Search universities for the autocomplete field.
     *
     * Public: also used on the login screen so interns can pick their
     * university before authenticating. Only active universities are
     * suggested; inactive ones stay available for existing intern records but
     * cannot be picked for new entries.
     */
    public function universities(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', '');

        $universities = University::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name']);

        return response()->json(
            $universities->map(fn (University $university): array => [
                'id' => $university->id,
                'name' => $university->name,
            ])
        );
    }

    /**
     * Search study programs for the autocomplete field, optionally filtered by
     * the selected university. Only active study programs are suggested.
     */
    public function studyPrograms(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', '');
        $universityId = $request->filled('university_id')
            ? (int) $request->query('university_id')
            : null;

        $programs = StudyProgram::query()
            ->active()
            ->forUniversity($universityId)
            ->search($term)
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'level']);

        return response()->json(
            $programs->map(fn (StudyProgram $program): array => [
                'id' => $program->id,
                'name' => $program->name,
                'level' => $program->level,
            ])
        );
    }

    /**
     * Search active divisions for the autocomplete field.
     */
    public function divisions(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $divisions = Division::query()
            ->active()
            ->when($term !== '', fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name']);

        return response()->json(
            $divisions->map(fn (Division $division): array => [
                'id' => $division->id,
                'name' => $division->name,
            ])
        );
    }

    /**
     * Quick-create a university directly from the autocomplete flow.
     *
     * Used by the intern form so an administrator can add a missing university
     * without leaving the page. Returns the created record so the frontend can
     * select it immediately.
     */
    public function storeUniversity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('universities', 'name')],
        ], [
            'name.required' => 'Nama perguruan tinggi wajib diisi.',
            'name.unique' => 'Perguruan tinggi tersebut sudah terdaftar.',
        ]);

        $university = University::create([
            'name' => trim($validated['name']),
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $university->id,
            'name' => $university->name,
        ], 201);
    }

    /**
     * Quick-create a study program under a university from the autocomplete
     * flow. A study program must always belong to a university.
     */
    public function storeStudyProgram(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:20'],
        ], [
            'university_id.required' => 'Perguruan tinggi wajib dipilih terlebih dahulu.',
            'university_id.exists' => 'Perguruan tinggi tidak valid.',
            'name.required' => 'Nama program studi wajib diisi.',
        ]);

        $program = StudyProgram::create([
            'university_id' => (int) $validated['university_id'],
            'name' => trim($validated['name']),
            'level' => isset($validated['level']) && $validated['level'] !== ''
                ? trim($validated['level'])
                : null,
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $program->id,
            'name' => $program->name,
            'level' => $program->level,
        ], 201);
    }
}
