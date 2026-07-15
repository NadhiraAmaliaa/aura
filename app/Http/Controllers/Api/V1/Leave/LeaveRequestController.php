<?php

namespace App\Http\Controllers\Api\V1\Leave;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Leave\LeaveRequestResource;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leave request endpoints for the AURA mobile app (interns only).
 *
 * Read-only here: the intern's own pending queue, processed history, and a
 * single request detail. Submission lives in a separate write endpoint, and
 * approval/rejection stays on the admin web app — the mobile client never
 * decides on a request.
 */
class LeaveRequestController extends Controller
{
    /**
     * List the authenticated intern's own leave requests, newest first.
     *
     * A `filter` query parameter narrows the list to the section the mobile
     * landing screen is showing:
     *  - `pending`  → status = pending ("Menunggu Persetujuan")
     *  - `history`  → status in {approved, rejected} ("Riwayat Pengajuan")
     *  - omitted    → all of the intern's requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->with('approver')
            ->orderByDesc('created_at');

        $filter = $request->query('filter');
        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        $perPage = $this->resolvePerPage($request->query('per_page'));

        $records = $query->paginate($perPage);

        return response()->json([
            'data' => [
                'items' => LeaveRequestResource::collection($records->items()),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'last_page' => $records->lastPage(),
                    'has_more' => $records->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Show a single leave request owned by the authenticated intern.
     */
    public function show(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        abort_if(
            (int) $leaveRequest->user_id !== (int) $request->user()->id,
            Response::HTTP_FORBIDDEN
        );

        $leaveRequest->load('approver');

        return response()->json([
            'data' => new LeaveRequestResource($leaveRequest),
        ]);
    }

    /**
     * Clamp the requested page size to a sane range (default 15, max 50).
     */
    private function resolvePerPage(mixed $value): int
    {
        $perPage = (int) $value;

        if ($perPage < 1) {
            return 15;
        }

        return min($perPage, 50);
    }
}
