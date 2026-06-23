<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInternRequest;
use App\Http\Requests\UpdateInternRequest;
use App\Models\Division;
use App\Models\Intern;
use App\Models\InternProgram;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InternController extends Controller
{
    /**
     * Listing tabs: active participants versus the archive of soft-deleted ones.
     */
    private const TAB_DATA = 'data';

    private const TAB_ARCHIVED = 'arsip';

    /**
     * Display a filtered, paginated listing of the interns.
     *
     * The participant information (university, study program, division and the
     * internship period) is shown directly in the table, so all the relevant
     * relations are eager-loaded. Filtering is expressed with the query builder
     * only (no raw SQL) so it stays portable across SQL Server, MySQL and
     * SQLite.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $programId = $request->filled('program') ? (int) $request->query('program') : null;
        $divisionId = $request->filled('division') ? (int) $request->query('division') : null;
        $status = (string) $request->query('status', '');
        $periodFrom = $request->filled('period_from') ? $request->date('period_from') : null;
        $periodTo = $request->filled('period_to') ? $request->date('period_to') : null;

        // The archive tab lists soft-deleted interns; the default tab lists the
        // active ones. Both share the same filters.
        $tab = $request->query('tab') === self::TAB_ARCHIVED ? self::TAB_ARCHIVED : self::TAB_DATA;

        // Supervisors are locked to the interns of their own division.
        if ($request->user()->isSupervisor()) {
            $divisionId = $request->user()->division_id;
        }

        $perPage = (int) $request->integer('perPage', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $interns = Intern::query()
            ->when($tab === self::TAB_ARCHIVED, fn ($query) => $query->onlyTrashed())
            ->with(['user', 'internProgram', 'universityRef', 'studyProgram', 'divisionRef'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'))
                        ->orWhere('nim', 'like', '%'.$search.'%');
                });
            })
            ->when($programId !== null, fn ($query) => $query->where('intern_program_id', $programId))
            ->when($divisionId !== null, fn ($query) => $query->where('division_id', $divisionId))
            // Filter by the date-derived status so the result matches the
            // effective status shown in the table, regardless of whether the
            // stored column has been realigned by the daily schedule yet.
            ->when($status !== '', fn ($query) => match ($status) {
                Intern::STATUS_UPCOMING => $query->upcomingOn(),
                Intern::STATUS_ACTIVE => $query->activeOn(),
                Intern::STATUS_COMPLETED => $query->completedOn(),
                Intern::STATUS_INACTIVE => $query->inactive(),
                default => $query,
            })
            // Internship period overlap: keep interns whose period intersects
            // the requested range. Null bounds are treated as open-ended.
            ->when($periodFrom !== null, function ($query) use ($periodFrom): void {
                $query->where(function ($q) use ($periodFrom): void {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', $periodFrom);
                });
            })
            ->when($periodTo !== null, function ($query) use ($periodTo): void {
                $query->where(function ($q) use ($periodTo): void {
                    $q->whereNull('start_date')->orWhereDate('start_date', '<=', $periodTo);
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Division scope is also applied to the tab counts so supervisors only
        // see their own totals.
        $countScope = fn ($query) => $query->when(
            $request->user()->isSupervisor(),
            fn ($q) => $q->where('division_id', $request->user()->division_id)
        );

        return Inertia::render('admin/Interns/Index', [
            'interns' => $interns,
            'programs' => InternProgram::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'perPage' => $perPage,
            'tab' => $tab,
            'tabCounts' => [
                self::TAB_DATA => $countScope(Intern::query())->count(),
                self::TAB_ARCHIVED => $countScope(Intern::onlyTrashed())->count(),
            ],
            'filters' => [
                'search' => $search,
                'program' => $programId,
                'division' => $divisionId,
                'status' => $status,
                'period_from' => $periodFrom?->toDateString(),
                'period_to' => $periodTo?->toDateString(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new intern.
     */
    public function create(): Response
    {
        return Inertia::render('admin/Interns/Create', [
            'programs' => InternProgram::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created intern in storage.
     */
    public function store(StoreInternRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'password' => $data['password'],
                'role' => 'intern',
            ]);

            $user->intern()->create($this->internAttributes($data));
        });

        return redirect()
            ->route('admin.interns.index')
            ->with('status', 'Peserta magang berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified intern.
     */
    public function edit(Intern $intern): Response
    {
        $intern->load(['user', 'universityRef', 'studyProgram', 'divisionRef']);

        return Inertia::render('admin/Interns/Edit', [
            'intern' => $intern,
            'programs' => InternProgram::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified intern in storage.
     */
    public function update(UpdateInternRequest $request, Intern $intern): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $intern): void {
            $userData = [
                'name' => $data['name'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $intern->user->update($userData);

            $intern->update($this->internAttributes($data));
        });

        return redirect()
            ->route('admin.interns.index')
            ->with('status', 'Peserta magang berhasil diperbarui.');
    }

    /**
     * Archive the specified intern (soft delete).
     *
     * The record is never destroyed: the linked user account, attendance
     * history and leave requests are all preserved so the participant can be
     * restored later and stays available in reports and exports.
     */
    public function destroy(Intern $intern): RedirectResponse
    {
        $intern->delete();

        return redirect()
            ->back()
            ->with('status', 'Peserta magang berhasil diarsipkan.');
    }

    /**
     * Restore a previously archived intern.
     */
    public function restore(Intern $intern): RedirectResponse
    {
        $intern->restore();

        return redirect()
            ->back()
            ->with('status', 'Peserta magang berhasil dipulihkan.');
    }

    /**
     * Build the intern attributes from validated data.
     *
     * The master-data references are authoritative; the legacy free-text
     * columns are also filled (denormalised) so existing reports and exports
     * that read them keep working and historical records stay human-readable.
     * The stored status is derived from the dates unless the administrator has
     * explicitly deactivated the intern.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function internAttributes(array $data): array
    {
        $university = University::find($data['university_id']);
        $studyProgram = StudyProgram::find($data['study_program_id']);
        $division = Division::find($data['division_id']);

        return [
            'intern_program_id' => $data['intern_program_id'],
            'university_id' => $data['university_id'],
            'study_program_id' => $data['study_program_id'],
            'division_id' => $data['division_id'],
            'nim' => $data['nim'],
            'phone' => $data['phone'],
            'university' => $university?->name,
            'major' => $studyProgram?->name,
            'division' => $division?->name,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $this->resolveStatus($data),
        ];
    }

    /**
     * Derive the stored status from the active flag and the internship dates.
     *
     * A deactivated intern is stored as INACTIVE. Otherwise the status is the
     * date-derived value (upcoming / active / completed) so it never needs to
     * be maintained by hand.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveStatus(array $data): string
    {
        if (! ($data['is_active'] ?? true)) {
            return Intern::STATUS_INACTIVE;
        }

        return (new Intern([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => Intern::STATUS_ACTIVE,
        ]))->effectiveStatus();
    }
}
