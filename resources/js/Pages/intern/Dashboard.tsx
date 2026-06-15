import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Attendance, LeaveRequest } from '@/types';
import { Head, Link } from '@inertiajs/react';
import TodayAttendanceCard from './Partials/TodayAttendanceCard';

export default function Dashboard({
    todayAttendance,
    todayLeave,
    expectedCheckOut,
}: {
    todayAttendance: Attendance | null;
    todayLeave: LeaveRequest | null;
    expectedCheckOut: string | null;
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <TodayAttendanceCard
                todayAttendance={todayAttendance}
                todayLeave={todayLeave}
                expectedCheckOut={expectedCheckOut}
            />

            <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Link
                    href={route('intern.attendance.index')}
                    className="rounded-lg bg-white p-6 shadow transition hover:shadow-md"
                >
                    <h3 className="font-semibold text-gray-900">
                        Riwayat Absensi
                    </h3>
                    <p className="mt-1 text-sm text-gray-500">
                        Lihat catatan kehadiran Anda.
                    </p>
                </Link>
                <Link
                    href={route('intern.leave-requests.index')}
                    className="rounded-lg bg-white p-6 shadow transition hover:shadow-md"
                >
                    <h3 className="font-semibold text-gray-900">
                        Pengajuan Izin
                    </h3>
                    <p className="mt-1 text-sm text-gray-500">
                        Ajukan izin atau sakit dan pantau statusnya.
                    </p>
                </Link>
            </div>
        </AuthenticatedLayout>
    );
}
