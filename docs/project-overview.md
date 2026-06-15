**Project Overview**

- **Name**: PTPN Intern Attendance
- **Purpose**: Manage intern attendance, leave requests, non-working days, and attendance recap exports for reporting.
- **Stack**: Laravel (PHP) backend, JavaScript frontend, uses database migrations, factories, and exports.
- **Assumptions**: The repo contains models `Attendance`, `Intern`, `InternProgram`, `LeaveRequest`, `NonWorkingDay`, and `User`. Field names are inferred from typical attendance systems and migration filenames.

**Insight / High-level behavior**

- Interns belong to an `InternProgram` (cohort or batch).
- `Attendance` records daily presence/absence per intern, with timestamps and status.
- `LeaveRequest` represents internship leave applications tied to interns and approved/handled by users.
- `NonWorkingDay` stores public holidays or organization non-working dates affecting attendance calculations.
- `User` represents system users (admins, approvers) and may also represent interns if implemented that way.

**Inferred Tables & Key Fields**

- `intern_programs`
    - `id` (PK)
    - `name`
    - `start_date` / `end_date` (optional)
    - `created_at`, `updated_at`

- `interns`
    - `id` (PK)
    - `intern_program_id` (FK -> `intern_programs.id`)
    - `name`
    - `email`
    - `phone` (optional)
    - `started_at` (optional)
    - `status` (active/inactive)
    - `created_at`, `updated_at`

- `attendances`
    - `id` (PK)
    - `intern_id` (FK -> `interns.id`)
    - `date` (date of record)
      **Project Overview (scanned from repository)**

    - **Name**: PTPN Intern Attendance
    - **Purpose**: Manage intern attendance, leave requests, non-working days, and attendance recap exports for reporting.
    - **Stack**: Laravel (PHP) backend, JavaScript frontend.

    This document was updated by scanning the repository's models and migrations to reflect the exact table columns, enums, and relationships implemented in code.

    **Models & Tables (exact from code/migrations)**
    - `users`
        - `id` (PK)
        - `name` (string)
        - `email` (string, unique)
        - `email_verified_at` (timestamp, nullable)
        - `password` (string, hashed)
        - `remember_token` (string)
        - `role` (enum: `admin`, `intern`) — added by migration
        - `created_at`, `updated_at`

    - `intern_programs`
        - `id` (PK)
        - `name` (string)
        - `description` (text, nullable)
        - `created_at`, `updated_at`

    - `interns`
        - `id` (PK)
        - `intern_program_id` (FK -> `intern_programs.id`, cascade on delete)
        - `user_id` (FK -> `users.id`, cascade on delete)
        - `nim` (string)
        - `phone` (string)
        - `university` (string)
        - `major` (string)
        - `division` (string)
        - `start_date` (date)
        - `end_date` (date)
        - `status` (enum: `active`, `inactive`, `completed`) default `active`
        - `created_at`, `updated_at`

    - `attendances`
        - `id` (PK)
        - `user_id` (FK -> `users.id`, cascade on delete)
        - `attendance_date` (date, indexed)
        - `check_in_time` (time, nullable)
        - `check_out_time` (time, nullable)
        - `check_in_latitude` (decimal 10,7, nullable)
        - `check_in_longitude` (decimal 10,7, nullable)
        - `check_out_latitude` (decimal 10,7, nullable)
        - `check_out_longitude` (decimal 10,7, nullable)
        - `status` (enum: `present`, `late`, `sick`, `permission`, `absent`) default `present`
        - `work_mode` (string(20), nullable) — added by later migration; allowed values enforced in model constants (`wfo`, `wfh`, `dinas`)
        - `notes` (text, nullable)
        - `created_at`, `updated_at`
        - Unique index: (`user_id`, `attendance_date`)

    - `leave_requests`
        - `id` (PK)
        - `user_id` (FK -> `users.id`, cascade on delete)
        - `request_number` (string, unique)
        - `type` (enum: `izin`, `sakit`)
        - `reason` (text)
        - `start_date` (date)
        - `end_date` (date)
        - `total_days` (unsigned integer)
        - `contact_phone` (string, nullable)
        - `address` (text, nullable)
        - `status` (enum: `pending`, `approved`, `rejected`) default `pending`
        - `admin_note` (text, nullable)
        - `approved_by` (FK -> `users.id`, nullable)
        - `approved_at` (timestamp, nullable)
        - `created_at`, `updated_at`

    - `non_working_days`
        - `id` (PK)
        - `date` (date, unique)
        - `name` (string)
        - `type` (enum: `national_holiday`, `collective_leave`, `company_holiday`)
        - `created_at`, `updated_at`

    **Relationships (from models & migrations)**
    - `users` 1---\* `attendances` (User hasMany Attendance via `user_id`)
    - `users` 1---\* `leave_requests` (User hasMany LeaveRequest via `user_id`)
    - `users` 1---1 `intern` (User hasOne Intern)
    - `intern_programs` 1---\* `interns` (InternProgram hasMany Intern)
    - `interns` \*---1 `users` (Intern belongsTo User via `user_id`)
    - `interns` \*---1 `intern_programs` (Intern belongsTo InternProgram via `intern_program_id`)
    - `leave_requests.approved_by` -> `users.id` (nullable FK to approver)

    Notes on constraints and behavior:
    - `attendances.user_id` uses `cascadeOnDelete()` in migration — deleting a user will delete attendances.
    - `interns` link both to `users` and `intern_programs` with cascade delete.
    - `leave_requests.approved_by` does not cascade (nullable) to avoid multiple cascade paths.

    **Mermaid ERD (exact schema)**

    ```mermaid
    erDiagram
        USERS ||--o{ ATTENDANCES : "has"
        USERS ||--o{ LEAVE_REQUESTS : "submits"
        USERS ||--o{ INTERNS : "owns"
        INTERNS }o--|| INTERN_PROGRAMS : "belongs_to"
        INTERNS ||--o{ ATTENDANCES : "(via user_id)"
        LEAVE_REQUESTS }o--|| USERS : "approved_by (nullable)"

        USERS : id PK
        USERS : name
        USERS : email UNIQUE
        USERS : role

        INTERN_PROGRAMS : id PK
        INTERN_PROGRAMS : name

        INTERNS : id PK
        INTERNS : intern_program_id FK
        INTERNS : user_id FK
        INTERNS : nim
        INTERNS : phone

        ATTENDANCES : id PK
        ATTENDANCES : user_id FK
        ATTENDANCES : attendance_date
        ATTENDANCES : check_in_time
        ATTENDANCES : check_out_time
        ATTENDANCES : status
        ATTENDANCES : work_mode
        ATTENDANCES : unique(user_id, attendance_date)

        LEAVE_REQUESTS : id PK
        LEAVE_REQUESTS : user_id FK
        LEAVE_REQUESTS : request_number UNIQUE
        LEAVE_REQUESTS : type
        LEAVE_REQUESTS : start_date
        LEAVE_REQUESTS : end_date
        LEAVE_REQUESTS : approved_by FK

        NON_WORKING_DAYS : id PK
        NON_WORKING_DAYS : date UNIQUE
        NON_WORKING_DAYS : type
    ```

    **Next steps**
    - I scanned models and migrations and updated this doc to match the code.
    - I can now regenerate the ERD as an image, add SQL create statements, or open a PR with this doc. Which would you like next?
