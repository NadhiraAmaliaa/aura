<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Immutable capture context for an attendance event.
 *
 * Carries the fields the mobile app records at the moment a check-in/out is
 * captured on the device. A null/empty context means a plain server-clock,
 * live-geofence action (the web Inertia flow or an immediate online request).
 *
 * The office snapshot ({@see $officeLatitude}, {@see $officeLongitude},
 * {@see $officeRadius}) is the office configuration the client validated
 * against at capture time. When present it is FROZEN onto the attendance
 * record and used for geofence validation instead of the live office, so a
 * later admin change to the office (moved pin or shrunk radius) never
 * re-decides a past offline event.
 */
final readonly class AttendanceCaptureContext
{
    public function __construct(
        public ?Carbon $capturedAt = null,
        public ?string $clientEventId = null,
        public ?int $officeId = null,
        public ?string $officeLatitude = null,
        public ?string $officeLongitude = null,
        public ?int $officeRadius = null,
        public ?string $officeName = null,
        public ?bool $autoTimeEnabled = null,
    ) {}

    /**
     * Whether the client sent a complete office geofence snapshot to validate
     * against (the offline-queue case).
     */
    public function hasOfficeSnapshot(): bool
    {
        return $this->officeLatitude !== null
            && $this->officeLongitude !== null
            && $this->officeRadius !== null;
    }
}
