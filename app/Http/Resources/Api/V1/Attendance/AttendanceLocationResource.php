<?php

namespace App\Http\Resources\Api\V1\Attendance;

use App\Models\AttendanceLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An active office attendance location for the mobile client.
 *
 * Coordinates are emitted as numbers (not the model's `decimal:7` strings) so
 * the client can feed them straight into a map SDK (e.g. Google Maps) and draw
 * the geofence circle from `radius` (metres).
 *
 * @mixin AttendanceLocation
 */
class AttendanceLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'radius' => (int) $this->radius,
        ];
    }
}
