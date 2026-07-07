<?php

namespace App\Services;

use App\Models\AttendanceLocation;
use App\Support\Geo;

/**
 * Evaluates a coordinate against the configured office attendance locations.
 *
 * Backs the WFO geofence: only WFO check-ins are validated on-site. The policy
 * is fail-closed — when no active location is configured the caller treats a
 * null result as "not allowed" (see [AttendanceService::checkIn]).
 */
class GeofenceService
{
    /**
     * Find the nearest active office location to the given coordinate.
     *
     * @return array{location: AttendanceLocation, distance: float}|null
     *         Null when there are no active locations configured.
     */
    public function nearestActive(float $latitude, float $longitude): ?array
    {
        $nearest = null;
        $nearestDistance = null;

        foreach (AttendanceLocation::active()->get() as $location) {
            $distance = Geo::haversineMeters(
                $latitude,
                $longitude,
                (float) $location->latitude,
                (float) $location->longitude,
            );

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearest = $location;
                $nearestDistance = $distance;
            }
        }

        if ($nearest === null) {
            return null;
        }

        return ['location' => $nearest, 'distance' => $nearestDistance];
    }
}
