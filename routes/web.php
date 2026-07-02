<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceLocationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\InternController;
use App\Http\Controllers\Admin\InternProgramController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\NonWorkingDayController;
use App\Http\Controllers\Admin\StudyProgramController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkingHourController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Intern\AttendanceController;
use App\Http\Controllers\Intern\DashboardController as InternDashboardController;
use App\Http\Controllers\Intern\LeaveRequestController;
use App\Http\Controllers\LeaveRequestPdfController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'welcome']);

// Public university lookup for the autocomplete on the login screen.
Route::get('/lookup/universities', [LookupController::class, 'universities'])->name('lookup.universities');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth', 'role:admin,supervisor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Read-only operational views. Supervisors share the administrator
        // pages but are scoped to their division inside the controllers.
        Route::get('/interns', [InternController::class, 'index'])->name('interns.index');
        Route::get('/interns/{intern}', [InternController::class, 'show'])->whereNumber('intern')->name('interns.show');
        Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::get('/leave-requests/{leaveRequest}', [AdminLeaveRequestController::class, 'show'])->name('leave-requests.show');
        Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
        Route::get('/attendances/export', [AdminAttendanceController::class, 'export'])->name('attendances.export');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Internal staff (admin & supervisor) account management.
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::resource('interns', InternController::class)->except(['show', 'index']);
        Route::patch('interns/{intern}/restore', [InternController::class, 'restore'])
            ->withTrashed()
            ->name('interns.restore');

        // Master data managed by administrators.
        Route::resource('intern-programs', InternProgramController::class)
            ->except(['show'])
            ->parameters(['intern-programs' => 'internProgram']);
        Route::resource('divisions', DivisionController::class)->except(['show']);
        Route::resource('universities', UniversityController::class)->except(['show']);
        Route::resource('study-programs', StudyProgramController::class)
            ->except(['show'])
            ->parameters(['study-programs' => 'study_program']);

        // Autocomplete lookups for the intern form (admin only).
        Route::get('/lookup/study-programs', [LookupController::class, 'studyPrograms'])->name('lookup.study-programs');
        Route::get('/lookup/divisions', [LookupController::class, 'divisions'])->name('lookup.divisions');

        // Quick-create endpoints used by the autocomplete on the intern form.
        Route::post('/lookup/universities', [LookupController::class, 'storeUniversity'])->name('lookup.universities.store');
        Route::post('/lookup/study-programs', [LookupController::class, 'storeStudyProgram'])->name('lookup.study-programs.store');
        Route::post('/lookup/divisions', [LookupController::class, 'storeDivision'])->name('lookup.divisions.store');

        Route::get('/attendances/{attendance}/edit', [AdminAttendanceController::class, 'edit'])->name('attendances.edit');
        Route::patch('/attendances/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendances.update');

        Route::resource('non-working-days', NonWorkingDayController::class)
            ->except(['show'])
            ->parameters(['non-working-days' => 'nonWorkingDay']);

        // Attendance settings (Master Absensi).
        Route::resource('working-hours', WorkingHourController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['working-hours' => 'workingHour']);
        Route::resource('attendance-locations', AttendanceLocationController::class)
            ->except(['show'])
            ->parameters(['attendance-locations' => 'attendanceLocation']);
    });

// Leave request decisions are owned by the division supervisor/mentor only.
// Administrators keep read-only access (index/show) for monitoring above.
Route::middleware(['auth', 'role:supervisor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::patch('/leave-requests/{leaveRequest}/approve', [AdminLeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::patch('/leave-requests/{leaveRequest}/reject', [AdminLeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    });

Route::middleware(['auth', 'role:intern', 'intern.active'])
    ->prefix('intern')
    ->name('intern.')
    ->group(function () {
        Route::get('/dashboard', [InternDashboardController::class, 'index'])->name('dashboard');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');

        Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
        Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
        Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    });

Route::middleware('auth')->group(function () {
    Route::get('/leave-requests/{leaveRequest}/pdf', LeaveRequestPdfController::class)->name('leave-requests.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
