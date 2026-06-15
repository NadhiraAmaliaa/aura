import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import { leaveTypeLabels, formatDate } from '@/lib/labels';
import { LeaveRequest } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Stats {
    total_interns: number;
    active_interns: number;
    present_today: number;
    pending_leaves: number;
}

const cards: { key: keyof Stats; label: string; color: string }[] = [
    { key: 'total_interns', label: 'Total Peserta', color: 'bg-green-700' },
    { key: 'active_interns', label: 'Peserta Aktif', color: 'bg-sky-600' },
    { key: 'present_today', label: 'Hadir Hari Ini', color: 'bg-emerald-600' },
    { key: 'pending_leaves', label: 'Izin Menunggu', color: 'bg-amber-600' },
];

export default function Dashboard({
    stats,
    recentLeaves,
}: {
    stats: Stats;
    recentLeaves: LeaveRequest[];
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

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {cards.map((card) => (
                    <div
                        key={card.key}
                        className="overflow-hidden rounded-lg bg-white shadow"
                    >
                        <div className={`${card.color} h-1.5`} />
                        <div className="p-5">
                            <p className="text-sm text-gray-500">
                                {card.label}
                            </p>
                            <p className="mt-1 text-3xl font-bold text-gray-900">
                                {stats[card.key]}
                            </p>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-6 rounded-lg bg-white p-6 shadow">
                <div className="mb-4 flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-800">
                        Pengajuan Izin Terbaru
                    </h3>
                    <Link
                        href={route('admin.leave-requests.index')}
                        className="text-sm font-medium text-green-700 hover:underline"
                    >
                        Lihat semua
                    </Link>
                </div>

                {recentLeaves.length === 0 ? (
                    <p className="text-sm text-gray-500">
                        Tidak ada pengajuan yang menunggu.
                    </p>
                ) : (
                    <ul className="divide-y divide-gray-100">
                        {recentLeaves.map((leave) => (
                            <li
                                key={leave.id}
                                className="flex items-center justify-between py-3"
                            >
                                <div>
                                    <Link
                                        href={route(
                                            'admin.leave-requests.show',
                                            leave.id,
                                        )}
                                        className="font-medium text-gray-900 hover:underline"
                                    >
                                        {leave.user?.name}
                                    </Link>
                                    <p className="text-sm text-gray-500">
                                        {formatDate(leave.start_date)} -{' '}
                                        {formatDate(leave.end_date)}
                                    </p>
                                </div>
                                <Badge className="bg-indigo-100 text-indigo-800">
                                    {leaveTypeLabels[leave.type]}
                                </Badge>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
