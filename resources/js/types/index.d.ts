import { Config as ZiggyConfig } from 'ziggy-js';

export type UserRole = 'admin' | 'intern';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    is_admin: boolean;
    is_intern: boolean;
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
    email: string;
    role: UserRole;
    email_verified_at?: string | null;
    intern?: Intern | null;
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

export type InternStatus = 'active' | 'inactive' | 'completed';

export interface Intern {
    id: number;
    user_id: number;
    intern_program_id: number | null;
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
}

export type AttendanceStatus =
    | 'present'
    | 'late'
    | 'sick'
    | 'permission'
    | 'absent';

export type WorkMode = 'wfo' | 'wfh' | 'dinas';

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

export type LeaveType = 'izin' | 'sakit';
export type LeaveStatus = 'pending' | 'approved' | 'rejected';

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
    | 'national_holiday'
    | 'collective_leave'
    | 'company_holiday';

export interface NonWorkingDay {
    id: number;
    date: string;
    name: string;
    type: NonWorkingDayType;
}

export interface RecapRow {
    intern: Intern;
    effective_working_days: number;
    hadir: number;
    wfo: number;
    wfh: number;
    dinas: number;
    izin: number;
    tidak_absen: number;
    terlambat: number;
    tidak_co: number;
    persen_terlambat: number;
    persen_tidak_absen: number;
}

export interface Recap {
    start_date: string;
    end_date: string;
    effective_working_days: number;
    rows: RecapRow[];
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
