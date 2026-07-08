<?php

namespace App\Services;

use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
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
    ): Attendance {
        $now ??= Carbon::now();
        $date = $now->copy()->startOfDay();

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
            throw AttendanceException::conflict('Anda sudah melakukan Check In hari ini.');
        }

        if (! Attendance::isCheckInAllowed($now, $workMode)) {
            $deadline = Attendance::checkInDeadline($now, $workMode);

            throw AttendanceException::unprocessable(
                'Check In untuk mode ini hanya dapat dilakukan hingga pukul '.$deadline.
                '. Waktu Check In telah terlewati.'
            );
        }

        // WFO check-ins are validated against the configured office locations
        // (geofencing). The policy is fail-closed: if no active location is
        // configured, WFO check-in is blocked (admins can still correct
        // attendance manually from the web).
        if (Attendance::requiresGeofence($workMode)) {
            $this->assertWithinOfficeGeofence($latitude, $longitude);
        }

        try {
            return Attendance::create([
                'user_id' => $user->id,
                'attendance_date' => $date,
                'check_in_time' => $now->format('H:i'),
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'status' => Attendance::determineStatus($now, $workMode),
                'work_mode' => $workMode,
            ]);
        } catch (UniqueConstraintViolationException) {
            // A concurrent request (double tap / retry on a flaky mobile
            // connection) already created today's record. Treat it as an
            // idempotent conflict instead of surfacing a 500.
            throw AttendanceException::conflict('Anda sudah melakukan Check In hari ini.');
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
    ): Attendance {
        $now ??= Carbon::now();
        $date = $now->copy()->startOfDay();

        $this->assertCanRecordAttendance($user, $date);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->first();

        if (! $attendance || ! $attendance->check_in_time) {
            throw AttendanceException::unprocessable(
                'Anda harus Check In terlebih dahulu sebelum Check Out.'
            );
        }

        if ($attendance->check_out_time) {
            throw AttendanceException::conflict('Anda sudah melakukan Check Out hari ini.');
        }

        $checkInMoment = $now->copy()
            ->setTimeFromTimeString($attendance->check_in_time->format('H:i:s'));

        if ($now->lessThanOrEqualTo($checkInMoment)) {
            throw AttendanceException::unprocessable(
                'Waktu Check Out harus setelah waktu Check In.'
            );
        }

        // The work mode was fixed at check-in; only WFO check-outs are
        // validated on-site against the configured office locations. WFH and
        // Dinas skip the geofence, matching the check-in policy.
        if (Attendance::requiresGeofence($attendance->work_mode)) {
            $this->assertWithinOfficeGeofence($latitude, $longitude);
        }

        $attendance->update([
            'check_out_time' => $now->format('H:i'),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
        ]);

        return $attendance->refresh();
    }

    /**
     * Guard that a WFO attendance action is inside an active office location's
     * radius.
     *
     * Fail-closed: a missing coordinate, or no active location configured,
     * blocks the action. Shared by WFO check-in and WFO check-out.
     *
     * @throws AttendanceException
     */
    private function assertWithinOfficeGeofence(?string $latitude, ?string $longitude): void
    {
        if ($latitude === null || $longitude === null) {
            throw AttendanceException::unprocessable(
                'Lokasi Anda wajib diaktifkan untuk absensi WFO.'
            );
        }

        $match = $this->geofence->nearestActive((float) $latitude, (float) $longitude);

        if ($match === null) {
            throw AttendanceException::unprocessable(
                'Lokasi kantor belum dikonfigurasi. Silakan hubungi administrator.'
            );
        }

        if ($match['distance'] > $match['location']->radius) {
            throw AttendanceException::unprocessable(sprintf(
                'Anda berada di luar radius lokasi kantor. Jarak Anda sekitar %d m dari %s (radius %d m).',
                (int) round($match['distance']),
                $match['location']->name,
                $match['location']->radius,
            ));
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
