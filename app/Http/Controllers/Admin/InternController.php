<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInternRequest;
use App\Http\Requests\UpdateInternRequest;
use App\Models\Intern;
use App\Models\InternProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternController extends Controller
{
    /**
     * Display a listing of the interns.
     */
    public function index(): View
    {
        $interns = Intern::with(['user', 'internProgram'])
            ->latest()
            ->paginate(10);

        return view('admin.interns.index', compact('interns'));
    }

    /**
     * Show the form for creating a new intern.
     */
    public function create(): View
    {
        $programs = InternProgram::orderBy('name')->get();

        return view('admin.interns.create', compact('programs'));
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
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'intern',
            ]);

            $user->intern()->create([
                'intern_program_id' => $data['intern_program_id'],
                'nim' => $data['nim'],
                'phone' => $data['phone'],
                'university' => $data['university'],
                'major' => $data['major'],
                'division' => $data['division'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
            ]);
        });

        return redirect()
            ->route('admin.interns.index')
            ->with('status', 'Intern created successfully.');
    }

    /**
     * Show the form for editing the specified intern.
     */
    public function edit(Intern $intern): View
    {
        $intern->load('user');
        $programs = InternProgram::orderBy('name')->get();

        return view('admin.interns.edit', compact('intern', 'programs'));
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
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $intern->user->update($userData);

            $intern->update([
                'intern_program_id' => $data['intern_program_id'],
                'nim' => $data['nim'],
                'phone' => $data['phone'],
                'university' => $data['university'],
                'major' => $data['major'],
                'division' => $data['division'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
            ]);
        });

        return redirect()
            ->route('admin.interns.index')
            ->with('status', 'Intern updated successfully.');
    }

    /**
     * Remove the specified intern and its linked user account.
     */
    public function destroy(Intern $intern): RedirectResponse
    {
        DB::transaction(function () use ($intern): void {
            // Deleting the user cascades to the intern record via the foreign key.
            $intern->user->delete();
        });

        return redirect()
            ->route('admin.interns.index')
            ->with('status', 'Intern deleted successfully.');
    }
}
