<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceLocationRequest;
use App\Http\Requests\UpdateAttendanceLocationRequest;
use App\Models\Attendance;
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
        $locations = AttendanceLocation::orderBy('name')->get();

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
     *
     * An office that has already been used for attendance is preserved so its
     * historical (snapshotted) records keep a valid reference; the admin is
     * asked to deactivate it instead of deleting it. Only an unreferenced
     * office can be hard-deleted.
     */
    public function destroy(AttendanceLocation $attendanceLocation): RedirectResponse
    {
        $isReferenced = Attendance::where('check_in_office_id', $attendanceLocation->id)
            ->orWhere('check_out_office_id', $attendanceLocation->id)
            ->exists();

        if ($isReferenced) {
            return redirect()
                ->route('admin.attendance-locations.index')
                ->with('error', 'Lokasi ini sudah pernah digunakan untuk absensi sehingga tidak dapat dihapus. Nonaktifkan lokasi ini untuk menyembunyikannya dari penggunaan.');
        }

        $attendanceLocation->delete();

        return redirect()
            ->route('admin.attendance-locations.index')
            ->with('status', 'Lokasi absensi berhasil dihapus.');
    }
}
