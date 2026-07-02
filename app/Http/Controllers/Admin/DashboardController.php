<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Intern;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the administrator / supervisor dashboard.
     *
     * Supervisors only see figures for the division they manage.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $divisionId = $user->isSupervisor() ? $user->division_id : null;

        $today = today();

        $stats = [
            'total_interns' => Intern::query()
                ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
                ->count(),
            'active_interns' => Intern::activeOn($today)
                ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
                ->count(),
            'present_today' => Attendance::whereDate('attendance_date', $today)
                ->whereIn('status', ['present', 'late'])
                ->when($divisionId, fn ($q) => $q->whereHas('user.intern', fn ($i) => $i->where('division_id', $divisionId)))
                ->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')
                ->when($divisionId, fn ($q) => $q->whereHas('user.intern', fn ($i) => $i->where('division_id', $divisionId)))
                ->count(),
        ];

        $recentLeaves = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->when($divisionId, fn ($q) => $q->whereHas('user.intern', fn ($i) => $i->where('division_id', $divisionId)))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recentLeaves' => $recentLeaves,
        ]);
    }
}
