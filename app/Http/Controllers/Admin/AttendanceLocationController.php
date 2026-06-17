<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceLocationRequest;
use App\Http\Requests\UpdateAttendanceLocationRequest;
use App\Models\AttendanceLocation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceLocationController extends Controller
{
    /**
     * Display a listing of the attendance locations.
     */
    public function index(): Response
    {
        $locations = AttendanceLocation::orderBy('name')->paginate(15);

        return Inertia::render('admin/AttendanceLocations/Index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new attendance location.
     */
    public function create(): Response
    {
        return Inertia::render('admin/AttendanceLocations/Create');
    }

    /**
     * Store a newly created attendance location in storage.
     */
    public function store(StoreAttendanceLocationRequest $request): RedirectResponse
    {
        AttendanceLocation::create($request->validated());

        return redirect()
            ->route('admin.attendance-locations.index')
            ->with('status', 'Lokasi absensi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified attendance location.
     */
    public function edit(AttendanceLocation $attendanceLocation): Response
    {
        return Inertia::render('admin/AttendanceLocations/Edit', [
            'location' => $attendanceLocation,
        ]);
    }

    /**
     * Update the specified attendance location in storage.
     */
    public function update(UpdateAttendanceLocationRequest $request, AttendanceLocation $attendanceLocation): RedirectResponse
    {
        $attendanceLocation->update($request->validated());

        return redirect()
            ->route('admin.attendance-locations.index')
            ->with('status', 'Lokasi absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified attendance location from storage.
     */
    public function destroy(AttendanceLocation $attendanceLocation): RedirectResponse
    {
        $attendanceLocation->delete();

        return redirect()
            ->route('admin.attendance-locations.index')
            ->with('status', 'Lokasi absensi berhasil dihapus.');
    }
}
