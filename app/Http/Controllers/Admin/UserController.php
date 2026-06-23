<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the internal staff accounts (admin & supervisor).
     *
     * Intern accounts are intentionally excluded; they are managed in the
     * Peserta Magang module.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) $request->integer('perPage', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $users = User::query()
            ->with('division:id,name')
            ->whereIn('role', ['admin', 'supervisor'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new internal staff account.
     */
    public function create(): Response
    {
        return Inertia::render('admin/Users/Create', [
            'divisions' => Division::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created internal staff account in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'nik' => $data['nik'],
            'password' => $data['password'],
            'role' => $data['role'],
            'division_id' => $data['division_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified internal staff account.
     */
    public function edit(User $user): Response
    {
        $user->load('division:id,name');

        return Inertia::render('admin/Users/Edit', [
            'user' => $user->only(['id', 'name', 'email', 'nik', 'role', 'is_active', 'division_id']),
            'divisions' => Division::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified internal staff account in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Prevent an administrator from locking themselves out by removing
        // their own admin role or deactivating their own account.
        $isSelf = $request->user()->is($user);

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'nik' => $data['nik'],
            'role' => $isSelf ? 'admin' : $data['role'],
            'division_id' => $isSelf ? null : ($data['division_id'] ?? null),
            'is_active' => $isSelf ? true : ($data['is_active'] ?? true),
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $user->update($attributes);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun pengguna berhasil diperbarui.');
    }
}
