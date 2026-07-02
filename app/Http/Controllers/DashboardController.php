<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function welcome(): Response
    {
        return Inertia::render('Welcome');
    }

    /**
     * Redirect an authenticated user to their role-specific dashboard.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }
}
