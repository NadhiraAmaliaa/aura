<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * university before authenticating.
     */
    public function universities(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', '');

        $universities = University::query()
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
     * the selected university.
     */
    public function studyPrograms(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', '');
        $universityId = $request->filled('university_id')
            ? (int) $request->query('university_id')
            : null;

        $programs = StudyProgram::query()
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
}
