# Attendance Work Modes (WFO / WFH / Dinas)

This document describes the check-in work mode feature added to the intern
attendance module, the business rules behind it, and the files involved.

## Overview

When an intern checks in, they must first choose a **work mode**:

| Mode  | Label (UI)                | Late rules | Check-in time limit | Location validation (geofence) |
|-------|---------------------------|:----------:|:-------------------:|:------------------------------:|
| WFO   | WFO (Bekerja dari Kantor) | Yes        | Yes                 | Planned (not implemented yet)  |
| WFH   | WFH (Bekerja dari Rumah)  | Yes        | Yes                 | No                             |
| Dinas | Dinas (Tugas Luar)        | No         | No (anytime)        | No                             |

There are **no shifts** for interns. WFO uses a single Normal working schedule.

`Izin` and `Sakit` are **not** check-in modes. They are handled separately by
the existing Leave Request feature (`LeaveRequest`). An approved leave for the
day blocks check-in, as before.

## Status rules

- **Dinas** → always `present` (never late), check-in allowed anytime/anywhere.
- **WFO / WFH** on a working day → `late` if checked in after the work start
  time (08:00), otherwise `present`. Check-in is blocked after the working-day
  end time (Mon–Thu 17:00, Fri 15:00).
- **Non-working day** (any mode) → `present`, no deadline. Non-working days are
  weekends plus registered holidays.

The attendance `status` values are unchanged: `present`, `late`, `sick`,
`permission`, `absent`. Work mode is stored independently of status.

## Geofencing (future work — not implemented)

Only WFO will later validate the intern's location against the office
coordinates and a radius. The hook `Attendance::requiresGeofence(string $workMode)`
returns `true` only for WFO, and the controller marks where the check happens.
No geofence package or radius config is installed yet, by design.

## Database

New migration: `database/migrations/2026_06_12_000001_add_work_mode_to_attendances_table.php`

Adds one nullable string column to `attendances`:

- `work_mode` — `wfo` | `wfh` | `dinas`

A plain `string` column (not `enum`) is used so the schema stays portable across
database engines (including **SQL Server 2008**) and so future modes do not
require dropping a check constraint. Allowed values are enforced in the
application layer (FormRequests + model constants). No raw SQL is used.

Run the migration in your environment:

```bash
php artisan migrate
```

## Files changed

- `app/Models/Attendance.php`
  - Converted `#[Fillable]` attribute to a plain `protected $fillable` array
    (broader PHP 8.2+ / Laravel compatibility); added `work_mode`.
  - Added constants: `WORK_MODE_WFO`, `WORK_MODE_WFH`, `WORK_MODE_DINAS`.
  - `determineStatus()` and `isCheckInAllowed()` accept `$workMode` (default
    WFO keeps backward compatibility).
  - Added `checkInDeadline()`, `requiresGeofence()`, `workModeLabels()` /
    `workModeLabel()`.
- `app/Http/Requests/CheckInRequest.php` (new) — validates `work_mode`.
- `app/Http/Controllers/Intern/AttendanceController.php` — uses `CheckInRequest`,
  passes the mode into the logic, stores it; geofence placeholder comment.
- `app/Http/Requests/UpdateAttendanceRequest.php` — admin can correct `work_mode`.
- `app/Http/Controllers/Admin/AttendanceController.php` — `work_mode` filter +
  saves the mode on update.
- Views (simple Blade, to be redesigned with Inertia later):
  - `resources/views/intern/attendance/index.blade.php` — work mode selection
    (WFO / WFH / Dinas), mode shown in status + history.
  - `resources/views/intern/dashboard.blade.php` — mode shown in today status.
  - `resources/views/admin/attendances/index.blade.php` — Mode column + filter.
  - `resources/views/admin/attendances/edit.blade.php` — mode display + editable
    field.
- `database/factories/AttendanceFactory.php` — generates a work mode.
- `tests/Feature/AttendanceLogicTest.php` (new) — covers the rules above.

## Recap

The admin attendance recap (`AttendanceRecapService`) now breaks the total
working attendance down by mode. Each row reports separate **WFO**, **WFH** and
**Dinas** counts (each counting `present`/`late` records for that mode). The
"Tidak Absen" calculation and the "% Terlambat" denominator use the combined
working total (WFO + WFH + Dinas), so the percentages stay consistent. The HTML
recap, the PDF export (`recap-pdf.blade.php`) and the Excel export
(`App\Exports\AttendanceRecapExport`) all show the three columns.

## Tests

```bash
php artisan test --filter=AttendanceLogicTest
```

Covers: WFO/WFH late rules, Dinas never-late & always-allowed, and WFO check-in
blocked after working-day end.

## Notes for company integration

- Code is database-agnostic (Eloquent / Carbon only, no raw SQL).
- Indonesian for user-facing text; English for code, fields, and routes.
- The work start and working-day end times live in `Attendance::WORK_START_TIME`
  and `Attendance::expectedCheckOutTime()` for easy adjustment.
- Other models still use the `#[Fillable]` attribute; converting them to plain
  `$fillable` arrays is recommended for the same compatibility reason if the
  target environment runs an older Laravel release.
