import {
    AttendanceStatus,
    LeaveStatus,
    LeaveType,
    NonWorkingDayType,
    WorkMode,
} from "@/types";

export const attendanceStatusLabels: Record<AttendanceStatus, string> = {
    present: "Hadir",
    late: "Terlambat",
    sick: "Sakit",
    permission: "Izin",
    absent: "Alpha",
};

export const attendanceStatusBadge: Record<AttendanceStatus, string> = {
    present: "bg-green-100 text-green-800",
    late: "bg-yellow-100 text-yellow-800",
    sick: "bg-blue-100 text-blue-800",
    permission: "bg-indigo-100 text-indigo-800",
    absent: "bg-red-100 text-red-800",
};

export const workModeLabels: Record<WorkMode, string> = {
    wfo: "WFO",
    wfh: "WFH",
    dinas: "Dinas",
};

export const workModeBadge: Record<WorkMode, string> = {
    wfo: "bg-green-100 text-green-800",
    wfh: "bg-sky-100 text-sky-800",
    dinas: "bg-purple-100 text-purple-800",
};

export const leaveTypeLabels: Record<LeaveType, string> = {
    izin: "Izin",
    sakit: "Sakit",
};

export const leaveStatusLabels: Record<LeaveStatus, string> = {
    pending: "Menunggu",
    approved: "Disetujui",
    rejected: "Ditolak",
};

export const leaveStatusBadge: Record<LeaveStatus, string> = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
};

export const nonWorkingDayTypeLabels: Record<NonWorkingDayType, string> = {
    national_holiday: "Hari Libur Nasional",
    collective_leave: "Cuti Bersama",
    company_holiday: "Libur Perusahaan",
};

export const dayOfWeekLabels: Record<number, string> = {
    1: "Senin",
    2: "Selasa",
    3: "Rabu",
    4: "Kamis",
    5: "Jumat",
    6: "Sabtu",
    7: "Minggu",
};

export const internStatusLabels: Record<string, string> = {
    upcoming: "Akan Datang",
    active: "Aktif",
    inactive: "Nonaktif",
    completed: "Selesai",
};

export const internStatusBadge: Record<string, string> = {
    upcoming: "bg-amber-100 text-amber-800",
    active: "bg-green-100 text-green-800",
    inactive: "bg-gray-100 text-gray-800",
    completed: "bg-blue-100 text-blue-800",
};

export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

export function formatTime(value: string | null | undefined): string {
    if (!value) {
        return "-";
    }

    // Accept "HH:mm:ss", "HH:mm", or full datetime strings.
    const timeMatch = value.match(/(\d{2}):(\d{2})/);

    if (timeMatch) {
        return `${timeMatch[1]}:${timeMatch[2]}`;
    }

    return value;
}
