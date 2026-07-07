<?php

namespace App\Support;

/**
 * Small geospatial helpers shared across attendance features (geofencing,
 * reporting). Pure functions only — no framework or database dependencies —
 * so they stay portable and trivially testable.
 */
class Geo
{
    /**
     * Great-circle distance between two coordinates, in metres (Haversine).
     */
    public static function haversineMeters(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
    ): float {
        $earthRadius = 6371000.0; // metres
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dPhi = deg2rad($lat2 - $lat1);
        $dLambda = deg2rad($lng2 - $lng1);

        $a = sin($dPhi / 2) ** 2
            + cos($phi1) * cos($phi2) * sin($dLambda / 2) ** 2;

        return 2.0 * $earthRadius * asin(sqrt($a));
    }
}
