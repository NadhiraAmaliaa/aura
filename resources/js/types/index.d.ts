import { Config as ZiggyConfig } from "ziggy-js";

export type UserRole = "admin" | "supervisor" | "intern";

export interface AuthUser {
    id: number;
    name: string;
    nik: string | null;
    role: UserRole;
    is_admin: boolean;
    is_supervisor: boolean;
    is_intern: boolean;
    division?: { id: number; name: string } | null;
    intern?: AuthIntern | null;
}

export interface AuthIntern {
    status: InternStatus;
    start_date: string | null;
    end_date: string | null;
    can_record_attendance: boolean;
    can_submit_leave: boolean;
    attendance_block_reason: string | null;
}

export interface User {
    id: number;
    name: string;
    nik: string | null;
    role: UserRole;
    intern?: Intern | null;
}

export interface ManagedUser {
    id: number;
    name: string;
    nik: string | null;
    role: "admin" | "supervisor";
    is_active: boolean;
    division_id: number | null;
    division?: { id: number; name: string } | null;
}

export interface Flash {
    success?: string | null;
    error?: string | null;
    status?: string | null;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface InternProgram {
    id: number;
    name: string;
    description?: string | null;
}

export interface University {
    id: number;
    name: string;
    lldikti?: string | null;
    is_active?: boolean;
}

export interface StudyProgram {
    id: number;
    university_id: number | null;
    name: string;
    level?: string | null;
    is_active?: boolean;
    university?: University | null;
}

export interface Division {
    id: number;
    name: string;
    is_active: boolean;
}

export type InternStatus = "upcoming" | "active" | "inactive" | "completed";

export interface Intern {
    id: number;
    user_id: number;
    intern_program_id: number | null;
    university_id: number | null;
    study_program_id: number | null;
    division_id: number | null;
    nim: string | null;
    phone: string | null;
    university: string | null;
    major: string | null;
    division: string | null;
    start_date: string | null;
    end_date: string | null;
    status: InternStatus;
    user?: User;
    intern_program?: InternProgram | null;
    university_ref?: University | null;
    study_program?: StudyProgram | null;
    division_ref?: Division | null;
}

export type AttendanceStatus =
    | "present"
    | "late"
    | "sick"
    | "permission"
    | "absent";

export type WorkMode = "wfo" | "wfh" | "dinas";

export interface Attendance {
    id: number;
    user_id: number;
    attendance_date: string;
    check_in_time: string | null;
    check_out_time: string | null;
    check_in_latitude: string | null;
    check_in_longitude: string | null;
    check_out_latitude: string | null;
    check_out_longitude: string | null;
    status: AttendanceStatus;
    work_mode: WorkMode | null;
    notes: string | null;
    user?: User;
}

export type LeaveType = "izin" | "sakit";
export type LeaveStatus = "pending" | "approved" | "rejected";

export interface LeaveRequest {
    id: number;
    user_id: number;
    request_number: string;
    type: LeaveType;
    reason: string | null;
    start_date: string;
    end_date: string;
    total_days: number;
    contact_phone: string | null;
    address: string | null;
    status: LeaveStatus;
    admin_note: string | null;
    approved_by: number | null;
    approved_at: string | null;
    created_at: string;
    user?: User;
    approver?: User | null;
}

export type NonWorkingDayType =
    | "national_holiday"
    | "collective_leave"
    | "company_holiday";

export interface NonWorkingDay {
    id: number;
    date: string;
    name: string;
    type: NonWorkingDayType;
}

export interface WorkingHour {
    id: number;
    day_of_week: number;
    start_time: string | null;
    end_time: string | null;
    is_working_day: boolean;
}

export interface AttendanceLocation {
    id: number;
    name: string;
    latitude: string;
    longitude: string;
    radius: number;
    is_active: boolean;
}

export type AttendanceReportCategory =
    | "wfo"
    | "wfh"
    | "dinas"
    | "izin"
    | "sakit"
    | "alpha"
    | "tidak_absen";

export interface AttendanceReportRow {
    intern_id: number;
    attendance_id: number | null;
    nim: string | null;
    nama: string | null;
    tanggal: string;
    program: string | null;
    divisi: string | null;
    hari: string;
    hari_kerja: boolean;
    category: AttendanceReportCategory;
    jenis_absen: string;
    is_late: boolean;
    check_in_schedule: string | null;
    check_in: string | null;
    check_in_lat: string | null;
    check_in_long: string | null;
    check_out_schedule: string | null;
    check_out: string | null;
    check_out_lat: string | null;
    check_out_long: string | null;
    jam_bekerja: string | null;
    jarak: number | null;
    status_kedatangan: string;
    status_kepulangan: string;
    keterlambatan: string;
    mood_in: string | null;
    mood_out: string | null;
}

export interface AttendanceReportSummary {
    total_peserta: number;
    total_hadir: number;
    terlambat: number;
    izin: number;
    tidak_hadir: number;
}

export interface AttendanceReportChart {
    wfo: number;
    wfh: number;
    dinas: number;
    izin: number;
    tidak_hadir: number;
}

export interface AttendanceReport {
    start_date: string;
    end_date: string;
    summary: AttendanceReportSummary;
    chart: AttendanceReportChart;
    rows: AttendanceReportRow[];
}

export interface AttendanceReportFilters {
    start_date: string;
    end_date: string;
    program: number | null;
    division: number | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: AuthUser | null;
    };
    flash: Flash;
    ziggy: ZiggyConfig & { location: string };
};
