import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import Pagination from '@/Components/Pagination';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import {
    formatDate,
    leaveStatusBadge,
    leaveStatusLabels,
    leaveTypeLabels,
} from '@/lib/labels';
import { LeaveRequest, Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Filters {
    status?: string;
    type?: string;
    search?: string;
}

export default function Index({
    leaveRequests,
    filters,
}: {
    leaveRequests: Paginated<LeaveRequest>;
    filters: Filters;
}) {
    const [form, setForm] = useState<Filters>({
        status: filters.status ?? '',
        type: filters.type ?? '',
        search: filters.search ?? '',
    });

    const applyFilters: FormEventHandler = (e) => {
        e.preventDefault();
        router.get(route('admin.leave-requests.index'), form, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Pengajuan Izin
                </h2>
            }
        >
            <Head title="Pengajuan Izin" />

            <form
                onSubmit={applyFilters}
                className="mb-4 grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4"
            >
                <SelectInput
                    className="w-full"
                    value={form.status}
                    onChange={(e) =>
                        setForm({ ...form, status: e.target.value })
                    }
                >
                    <option value="">Semua Status</option>
                    {Object.entries(leaveStatusLabels).map(([value, label]) => (
                        <option key={value} value={value}>
                            {label}
                        </option>
                    ))}
                </SelectInput>
                <SelectInput
                    className="w-full"
                    value={form.type}
                    onChange={(e) => setForm({ ...form, type: e.target.value })}
                >
                    <option value="">Semua Jenis</option>
                    {Object.entries(leaveTypeLabels).map(([value, label]) => (
                        <option key={value} value={value}>
                            {label}
                        </option>
                    ))}
                </SelectInput>
                <TextInput
                    type="text"
                    placeholder="Cari nomor / nama"
                    className="w-full"
                    value={form.search}
                    onChange={(e) =>
                        setForm({ ...form, search: e.target.value })
                    }
                />
                <button
                    type="submit"
                    className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                >
                    Filter
                </button>
            </form>

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nomor
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jenis
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Periode
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {leaveRequests.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Tidak ada pengajuan.
                                    </td>
                                </tr>
                            ) : (
                                leaveRequests.data.map((leave) => (
                                    <tr key={leave.id}>
                                        <td className="px-4 py-3 text-sm font-medium text-gray-700">
                                            {leave.request_number}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-gray-900">
                                                {leave.user?.name}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {leave.user?.intern?.nim}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {leaveTypeLabels[leave.type]}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {formatDate(leave.start_date)} -{' '}
                                            {formatDate(leave.end_date)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    leaveStatusBadge[
                                                        leave.status
                                                    ]
                                                }
                                            >
                                                {leaveStatusLabels[
                                                    leave.status
                                                ]}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    'admin.leave-requests.show',
                                                    leave.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
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

                <div className="border-t border-gray-100 px-4 py-3">
                    <Pagination links={leaveRequests.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
