<?php

namespace App\Services;

use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Geo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

/**
 * The single home for attendance check-in / check-out business rules.
 *
 * Shared by the Inertia web controller and the mobile API so both enforce the
 * same guards (internship period, approved leave, one record per day, check-in
 * deadline, check-out ordering). Rule violations are raised as
 * [AttendanceException] for the caller to translate into its response format.
 */
class AttendanceService
{
    /**
     * Allowed clock skew (minutes) for a client-captured timestamp that is
     * slightly ahead of the server clock.
     */
    private const CAPTURED_AT_FUTURE_SKEW_MINUTES = 2;

    /**
     * How many days a queued offline attendance event may still be synced.
     */
    private const OFFLINE_RETENTION_DAYS = 5;

    public function __construct(
        private readonly GeofenceService $geofence,
    ) {}

    /**
     * Record today's check-in for the intern.
     *
     * @throws AttendanceException when a business rule blocks the check-in.
     */
    public function checkIn(
        User $user,
        string $workMode,
        ?string $latitude = null,
        ?string $longitude = null,
        ?Carbon $now = null,
        ?AttendanceCaptureContext $capture = null,
    ): Attendance {
        $now ??= Carbon::now();
        $capture ??= new AttendanceCaptureContext();

        // captured_at (when the button was pressed on the device) is
        // authoritative for the attendance date and the stored check-in time.
        // When absent (web / immediate online) the server clock is used.
        $moment = $capture->capturedAt ?? $now;

        // Replaying an already-accepted event returns its record unchanged, so a
        // retried sync never double-records or trips a later guard.
        if ($capture->clientEventId !== null) {
            $replay = Attendance::where('check_in_client_id', $capture->clientEventId)->first();
            if ($replay !== null) {
                return $replay;
            }
        }

        if ($capture->capturedAt !== null) {
            $this->assertCapturedAtIsAcceptable($capture->capturedAt, $now);
        }

        $this->assertAutomaticTimeEnabled($capture->autoTimeEnabled);

        $date = $moment->copy()->startOfDay();

        $this->assertCanRecordAttendance($user, $date);

        $leave = LeaveRequest::approvedCovering($user->id, $date)->first();
        if ($leave) {
            throw AttendanceException::unprocessable(
                'Hari ini Anda sedang dalam masa '.$leave->typeLabel().
                ' yang telah disetujui, sehingga tidak dapat melakukan Check In.'
            );
        }

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->first();
        if ($existing) {
            throw AttendanceException::conflict('Anda sudah melakukan check in hari ini.');
        }

        // Late / on-time evaluation is measured against the captured moment but
        // still follows the current, dynamic working-hour rules (an admin change
        // to the schedule applies retroactively, matching the web app).
        if (! Attendance::isCheckInAllowed($moment, $workMode)) {
            $deadline = Attendance::checkInDeadline($moment, $workMode);

            throw AttendanceException::unprocessable(
                'Check In untuk mode ini hanya dapat dilakukan hingga pukul '.$deadline.
                '. Waktu Check In telah terlewati.'
            );
        }

        // WFO check-ins are validated against the configured office locations
        // (geofencing). The policy is fail-closed: if no active location is
        // configured, WFO check-in is blocked (admins can still correct
        // attendance manually from the web). For offline events the captured
        // office snapshot is used as-is; it is never re-captured at sync time.
        $geofence = null;
        if (Attendance::requiresGeofence($workMode)) {
            $geofence = $this->assertWithinOfficeGeofence($latitude, $longitude, $capture);
        }

        try {
            return Attendance::create([
                'user_id' => $user->id,
                'attendance_date' => $date,
                'check_in_time' => $moment->format('H:i'),
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'status' => Attendance::determineStatus($moment, $workMode),
                'work_mode' => $workMode,
                'check_in_client_id' => $capture->clientEventId,
                'check_in_captured_at' => $capture->capturedAt,
                'check_in_synced_at' => $capture->capturedAt !== null ? $now : null,
                'check_in_office_id' => $geofence['office_id'] ?? null,
                'check_in_office_latitude' => $geofence['latitude'] ?? null,
                'check_in_office_longitude' => $geofence['longitude'] ?? null,
                'check_in_office_radius' => $geofence['radius'] ?? null,
                'check_in_office_name' => $geofence['name'] ?? null,
                'check_in_auto_time' => $capture->autoTimeEnabled,
            ]);
        } catch (UniqueConstraintViolationException) {
            // A concurrent request (double tap / retry on a flaky mobile
            // connection) already created today's record or reused this event
            // id. Return the event's record when it is a replay; otherwise it is
            // another check-in for the same day -> conflict.
            if ($capture->clientEventId !== null) {
                $replay = Attendance::where('check_in_client_id', $capture->clientEventId)->first();
                if ($replay !== null) {
                    return $replay;
                }
            }

            throw AttendanceException::conflict('Anda sudah melakukan check in hari ini.');
        }
    }

    /**
     * Record today's check-out for the intern.
     *
     * @throws AttendanceException when a business rule blocks the check-out.
     */
    public function checkOut(
        User $user,
        ?string $latitude = null,
        ?string $longitude = null,
        ?Carbon $now = null,
        ?AttendanceCaptureContext $capture = null,
    ): Attendance {
        $now ??= Carbon::now();
        $capture ??= new AttendanceCaptureContext();

        // captured_at is authoritative for the stored check-out time and the
        // day the check-out belongs to.
        $moment = $capture->capturedAt ?? $now;

        // Idempotent replay of an already-applied check-out event.
        if ($capture->clientEventId !== null) {
            $replay = Attendance::where('check_out_client_id', $capture->clientEventId)->first();
            if ($replay !== null) {
                return $replay;
            }
        }

        if ($capture->capturedAt !== null) {
            $this->assertCapturedAtIsAcceptable($capture->capturedAt, $now);
        }

        $this->assertAutomaticTimeEnabled($capture->autoTimeEnabled);

        $date = $moment->copy()->startOfDay();

        $this->assertCanRecordAttendance($user, $date);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->first();

        if (! $attendance || ! $attendance->check_in_time) {
            throw AttendanceException::unprocessable(
                'Anda harus Check In terlebih dahulu sebelum Check Out.'
            );
        }

        $checkInMoment = $moment->copy()
            ->setTimeFromTimeString($attendance->check_in_time->format('H:i:s'));

        if ($moment->lessThanOrEqualTo($checkInMoment)) {
            throw AttendanceException::unprocessable(
                'Waktu Check Out harus setelah waktu Check In.'
            );
        }

        // Last-write-wins by captured time: a repeated check-out overwrites the
        // stored one, but only when it is strictly newer. An older or delayed
        // event (e.g. a late offline sync arriving after a fresher one) must
        // never move the recorded check-out backwards, so it is accepted as a
        // no-op and returns the current record unchanged.
        if ($attendance->check_out_time !== null) {
            $existingCheckOutMoment = $attendance->check_out_captured_at
                ?? $moment->copy()->setTimeFromTimeString($attendance->check_out_time->format('H:i:s'));

            if ($moment->lessThanOrEqualTo($existingCheckOutMoment)) {
                return $attendance;
            }
        }

        // The work mode was fixed at check-in; only WFO check-outs are
        // validated on-site against the configured office locations. WFH and
        // Dinas skip the geofence, matching the check-in policy.
        $geofence = null;
        if (Attendance::requiresGeofence($attendance->work_mode)) {
            $geofence = $this->assertWithinOfficeGeofence($latitude, $longitude, $capture);
        }

        $attendance->update([
            'check_out_time' => $moment->format('H:i'),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_client_id' => $capture->clientEventId,
            'check_out_captured_at' => $capture->capturedAt,
            'check_out_synced_at' => $capture->capturedAt !== null ? $now : null,
            'check_out_office_id' => $geofence['office_id'] ?? null,
            'check_out_office_latitude' => $geofence['latitude'] ?? null,
            'check_out_office_longitude' => $geofence['longitude'] ?? null,
            'check_out_office_radius' => $geofence['radius'] ?? null,
            'check_out_office_name' => $geofence['name'] ?? null,
            'check_out_auto_time' => $capture->autoTimeEnabled,
        ]);

        return $attendance->refresh();
    }

    /**
     * Guard that a WFO attendance action is inside the applicable office
     * radius, and return the office geofence snapshot to persist on the record.
     *
     * Fail-closed: a missing coordinate, or no active location configured (for
     * the online flow), blocks the action. Shared by WFO check-in and check-out.
     *
     * When the client supplied a full office snapshot (the offline queue), the
     * captured coordinates are validated against that FROZEN configuration, so a
     * later admin change to the office never re-decides a past offline event.
     * Otherwise (web / immediate online) the live nearest/selected active office
     * is used and its current configuration is snapshotted onto the record.
     *
     * @return array{office_id: int|null, name: string|null, latitude: string, longitude: string, radius: int}
     *
     * @throws AttendanceException
     */
    private function assertWithinOfficeGeofence(
        ?string $latitude,
        ?string $longitude,
        AttendanceCaptureContext $capture,
    ): array {
        if ($latitude === null || $longitude === null) {
            throw AttendanceException::unprocessable(
                'Lokasi Anda wajib diaktifkan untuk absensi WFO.'
            );
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;

        // Offline queue: validate against the frozen snapshot the device
        // captured, not the (possibly since-changed) live office.
        if ($capture->hasOfficeSnapshot()) {
            $distance = Geo::haversineMeters(
                $lat,
                $lng,
                (float) $capture->officeLatitude,
                (float) $capture->officeLongitude,
            );

            if ($distance > $capture->officeRadius) {
                throw AttendanceException::unprocessable(sprintf(
                    'Anda berada di luar radius lokasi kantor. Jarak Anda sekitar %d m (radius %d m).',
                    (int) round($distance),
                    $capture->officeRadius,
                ));
            }

            return [
                'office_id' => $this->persistableOfficeId($capture->officeId),
                'name' => $capture->officeName,
                'latitude' => $capture->officeLatitude,
                'longitude' => $capture->officeLongitude,
                'radius' => $capture->officeRadius,
            ];
        }

        // Online / web: validate against the captured office when known, else
        // the nearest active office, and snapshot its live configuration.
        $match = $capture->officeId !== null
            ? $this->geofence->matchForOffice($lat, $lng, $capture->officeId)
            : null;
        $match ??= $this->geofence->nearestActive($lat, $lng);

        if ($match === null) {
            throw AttendanceException::unprocessable(
                'Lokasi kantor belum dikonfigurasi. Silakan hubungi administrator.'
            );
        }

        $office = $match['location'];

        if ($match['distance'] > $office->radius) {
            throw AttendanceException::unprocessable(sprintf(
                'Anda berada di luar radius lokasi kantor. Jarak Anda sekitar %d m dari %s (radius %d m).',
                (int) round($match['distance']),
                $office->name,
                $office->radius,
            ));
        }

        return [
            'office_id' => $office->id,
            'name' => $office->name,
            'latitude' => (string) $office->latitude,
            'longitude' => (string) $office->longitude,
            'radius' => (int) $office->radius,
        ];
    }

    /**
     * Resolve a captured office id to one that is safe to store as a foreign
     * key.
     *
     * An offline event may reference an office that was hard-deleted (while it
     * was still unreferenced) before this event synced. In that case the
     * frozen snapshot still governs the geofence decision, but the FK is stored
     * as null so the insert does not violate the constraint.
     */
    private function persistableOfficeId(?int $officeId): ?int
    {
        if ($officeId === null) {
            return null;
        }

        return AttendanceLocation::whereKey($officeId)->exists() ? $officeId : null;
    }

    /**
     * Guard that a client-captured timestamp is plausible before it is trusted
     * as authoritative.
     *
     * Rejects a capture that is in the future (beyond a small clock skew) or
     * older than the offline retention window. This is the server-side backstop
     * for the client's "automatic date & time" requirement, which cannot be
     * verified on every platform.
     *
     * @throws AttendanceException
     */
    private function assertCapturedAtIsAcceptable(Carbon $capturedAt, Carbon $now): void
    {
        if ($capturedAt->greaterThan($now->copy()->addMinutes(self::CAPTURED_AT_FUTURE_SKEW_MINUTES))) {
            throw AttendanceException::unprocessable(
                'Waktu perangkat tidak valid. Aktifkan Tanggal & Waktu otomatis, lalu coba lagi.'
            );
        }

        if ($capturedAt->lessThan($now->copy()->subDays(self::OFFLINE_RETENTION_DAYS))) {
            throw AttendanceException::unprocessable(
                'Absensi ini sudah kedaluwarsa (lebih dari '.self::OFFLINE_RETENTION_DAYS.
                ' hari) dan tidak dapat disinkronkan.'
            );
        }
    }

    /**
     * Enforce the client's "automatic date & time" requirement.
     *
     * The mobile client hard-blocks capture when the device clock is manual and
     * reports the result here. An explicit `false` is rejected; `null` (web,
     * iOS, or an unverifiable device) stays backward-compatible and is allowed,
     * relying on the captured_at skew/retention guards instead.
     *
     * @throws AttendanceException
     */
    private function assertAutomaticTimeEnabled(?bool $autoTimeEnabled): void
    {
        if ($autoTimeEnabled === false) {
            throw AttendanceException::unprocessable(
                'Waktu perangkat tidak valid. Aktifkan Tanggal & Waktu otomatis, lalu coba lagi.'
            );
        }
    }

    /**
     * Guard that the intern profile exists and is eligible to record
     * attendance on the given date.
     *
     * @throws AttendanceException
     */
    private function assertCanRecordAttendance(User $user, Carbon $date): void
    {
        $intern = $user->intern;

        if (! $intern || ! $intern->canRecordAttendanceOn($date)) {
            throw AttendanceException::unprocessable(
                $intern?->attendanceBlockReason($date)
                    ?? 'Profil magang Anda belum lengkap. Silakan hubungi administrator.'
            );
        }
    }
}
