<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\JsonResponse;

class UniversityController extends Controller
{
    /**
     * List the universities selectable on the mobile login screen.
     *
     * Public (no authentication): an intern must pick their university before
     * they can sign in. Only universities that currently have at least one
     * intern who may still access the portal (upcoming or active) are
     * returned, so the dropdown stays short, never exposes the full master
     * table, and drops universities whose interns have all finished or been
     * deactivated. The portal-access rule is reused from the Intern model
     * ({@see \App\Models\Intern::scopeCanAccessPortalOn()}) so it stays
     * consistent with the login gate. Deliberately does not apply the
     * university `active` scope: an eligible intern may belong to a university
     * that was later deactivated, and they must still be able to log in.
     * Results are ordered alphabetically by name and use the query builder
     * only (no raw SQL) so the endpoint stays portable.
     */
    public function index(): JsonResponse
    {
        $universities = University::query()
            ->whereHas('interns', function ($query): void {
                $query->canAccessPortalOn();
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(
            $universities->map(fn (University $university): array => [
                'id' => $university->id,
                'name' => $university->name,
            ])
        );
    }
}
