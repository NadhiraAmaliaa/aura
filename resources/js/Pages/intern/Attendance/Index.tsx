import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import Pagination from '@/Components/Pagination';
import TodayAttendanceCard from '../Partials/TodayAttendanceCard';
import {
    attendanceStatusBadge,
    attendanceStatusLabels,
    formatDate,
    formatTime,
    workModeBadge,
    workModeLabels,
} from '@/lib/labels';
import { Attendance, LeaveRequest, Paginated } from '@/types';
import { Head } from '@inertiajs/react';

export default function Index({
    todayAttendance,
    todayLeave,
    expectedCheckOut,
    history,
}: {
    todayAttendance: Attendance | null;
    todayLeave: LeaveRequest | null;
    expectedCheckOut: string | null;
    history: Paginated<Attendance>;
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Absensi
                </h2>
            }
        >
            <Head title="Absensi" />

            <TodayAttendanceCard
                todayAttendance={todayAttendance}
                todayLeave={todayLeave}
                expectedCheckOut={expectedCheckOut}
            />

            <div className="mt-6 rounded-lg bg-white shadow">
                <div className="border-b border-gray-100 px-6 py-4">
                    <h3 className="text-lg font-semibold text-gray-900">
                        Riwayat Absensi
                    </h3>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th className="px-6 py-3">Tanggal</th>
                                <th className="px-6 py-3">Masuk</th>
                                <th className="px-6 py-3">Keluar</th>
                                <th className="px-6 py-3">Mode</th>
                                <th className="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {history.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        Belum ada riwayat absensi.
                                    </td>
                                </tr>
                            ) : (
                                history.data.map((item) => (
                                    <tr key={item.id}>
                                        <td className="px-6 py-3 text-gray-900">
                                            {formatDate(item.attendance_date)}
                                        </td>
                                        <td className="px-6 py-3 text-gray-600">
                                            {formatTime(item.check_in_time) || '-'}
                                        </td>
                                        <td className="px-6 py-3 text-gray-600">
                                            {formatTime(item.check_out_time) ||
                                                '-'}
                                        </td>
                                        <td className="px-6 py-3">
                                            {item.work_mode ? (
                                                <Badge
                                                    className={
                                                        workModeBadge[
                                                            item.work_mode
                                                        ]
                                                    }
                                                >
                                                    {
                                                        workModeLabels[
                                                            item.work_mode
                                                        ]
                                                    }
                                                </Badge>
                                            ) : (
                                                '-'
                                            )}
                                        </td>
                                        <td className="px-6 py-3">
                                            <Badge
                                                className={
                                                    attendanceStatusBadge[
                                                        item.status
                                                    ]
                                                }
                                            >
                                                {
                                                    attendanceStatusLabels[
                                                        item.status
                                                    ]
                                                }
                                            </Badge>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="px-6 py-4">
                    <Pagination links={history.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
