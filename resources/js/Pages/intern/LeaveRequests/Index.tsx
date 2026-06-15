import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import Pagination from '@/Components/Pagination';
import {
    formatDate,
    leaveStatusBadge,
    leaveStatusLabels,
    leaveTypeLabels,
} from '@/lib/labels';
import { LeaveRequest, Paginated } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Index({
    leaveRequests,
}: {
    leaveRequests: Paginated<LeaveRequest>;
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Pengajuan Izin
                </h2>
            }
        >
            <Head title="Pengajuan Izin" />

            <div className="mb-4 flex justify-end">
                <Link
                    href={route('intern.leave-requests.create')}
                    className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                >
                    Ajukan Izin
                </Link>
            </div>

            <div className="rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th className="px-6 py-3">No. Pengajuan</th>
                                <th className="px-6 py-3">Jenis</th>
                                <th className="px-6 py-3">Periode</th>
                                <th className="px-6 py-3">Durasi</th>
                                <th className="px-6 py-3">Status</th>
                                <th className="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {leaveRequests.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-6 py-8 text-center text-gray-500"
                                    >
                                        Belum ada pengajuan izin.
                                    </td>
                                </tr>
                            ) : (
                                leaveRequests.data.map((item) => (
                                    <tr key={item.id}>
                                        <td className="px-6 py-3 font-medium text-gray-900">
                                            {item.request_number}
                                        </td>
                                        <td className="px-6 py-3 text-gray-600">
                                            {leaveTypeLabels[item.type]}
                                        </td>
                                        <td className="px-6 py-3 text-gray-600">
                                            {formatDate(item.start_date)} &ndash;{' '}
                                            {formatDate(item.end_date)}
                                        </td>
                                        <td className="px-6 py-3 text-gray-600">
                                            {item.total_days} hari
                                        </td>
                                        <td className="px-6 py-3">
                                            <Badge
                                                className={
                                                    leaveStatusBadge[
                                                        item.status
                                                    ]
                                                }
                                            >
                                                {leaveStatusLabels[item.status]}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-3 text-right">
                                            <Link
                                                href={route(
                                                    'intern.leave-requests.show',
                                                    item.id,
                                                )}
                                                className="text-sm font-medium text-green-700 hover:underline"
                                            >
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="px-6 py-4">
                    <Pagination links={leaveRequests.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
