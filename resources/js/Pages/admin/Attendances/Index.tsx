import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import Pagination from '@/Components/Pagination';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import {
    attendanceStatusBadge,
    attendanceStatusLabels,
    formatDate,
    formatTime,
    workModeBadge,
    workModeLabels,
} from '@/lib/labels';
import { Attendance, InternProgram, Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Filters {
    date?: string;
    status?: string;
    work_mode?: string;
    program?: string;
    search?: string;
}

export default function Index({
    attendances,
    programs,
    filters,
}: {
    attendances: Paginated<Attendance>;
    programs: InternProgram[];
    filters: Filters;
}) {
    const [form, setForm] = useState<Filters>({
        date: filters.date ?? '',
        status: filters.status ?? '',
        work_mode: filters.work_mode ?? '',
        program: filters.program ?? '',
        search: filters.search ?? '',
    });

    const applyFilters: FormEventHandler = (e) => {
        e.preventDefault();
        router.get(route('admin.attendances.index'), form, {
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(route('admin.attendances.index'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Absensi
                </h2>
            }
        >
            <Head title="Absensi" />

            <form
                onSubmit={applyFilters}
                className="mb-4 grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-2 lg:grid-cols-6"
            >
                <TextInput
                    type="date"
                    className="w-full"
                    value={form.date}
                    onChange={(e) => setForm({ ...form, date: e.target.value })}
                />
                <SelectInput
                    className="w-full"
                    value={form.status}
                    onChange={(e) =>
                        setForm({ ...form, status: e.target.value })
                    }
                >
                    <option value="">Semua Status</option>
                    {Object.entries(attendanceStatusLabels).map(
                        ([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ),
                    )}
                </SelectInput>
                <SelectInput
                    className="w-full"
                    value={form.work_mode}
                    onChange={(e) =>
                        setForm({ ...form, work_mode: e.target.value })
                    }
                >
                    <option value="">Semua Mode</option>
                    {Object.entries(workModeLabels).map(([value, label]) => (
                        <option key={value} value={value}>
                            {label}
                        </option>
                    ))}
                </SelectInput>
                <SelectInput
                    className="w-full"
                    value={form.program}
                    onChange={(e) =>
                        setForm({ ...form, program: e.target.value })
                    }
                >
                    <option value="">Semua Program</option>
                    {programs.map((program) => (
                        <option key={program.id} value={program.id}>
                            {program.name}
                        </option>
                    ))}
                </SelectInput>
                <TextInput
                    type="text"
                    placeholder="Cari nama / NIM"
                    className="w-full"
                    value={form.search}
                    onChange={(e) =>
                        setForm({ ...form, search: e.target.value })
                    }
                />
                <div className="flex gap-2">
                    <button
                        type="submit"
                        className="flex-1 rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Filter
                    </button>
                    <button
                        type="button"
                        onClick={resetFilters}
                        className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Reset
                    </button>
                </div>
            </form>

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Tanggal
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Masuk
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Pulang
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Mode
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
                            {attendances.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Tidak ada data absensi.
                                    </td>
                                </tr>
                            ) : (
                                attendances.data.map((attendance) => (
                                    <tr key={attendance.id}>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {formatDate(
                                                attendance.attendance_date,
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-gray-900">
                                                {attendance.user?.name}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {attendance.user?.intern?.nim}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {formatTime(
                                                attendance.check_in_time,
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {formatTime(
                                                attendance.check_out_time,
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {attendance.work_mode ? (
                                                <Badge
                                                    className={
                                                        workModeBadge[
                                                            attendance.work_mode
                                                        ]
                                                    }
                                                >
                                                    {
                                                        workModeLabels[
                                                            attendance.work_mode
                                                        ]
                                                    }
                                                </Badge>
                                            ) : (
                                                <span className="text-sm text-gray-400">
                                                    -
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    attendanceStatusBadge[
                                                        attendance.status
                                                    ]
                                                }
                                            >
                                                {
                                                    attendanceStatusLabels[
                                                        attendance.status
                                                    ]
                                                }
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    'admin.attendances.edit',
                                                    attendance.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Koreksi
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="border-t border-gray-100 px-4 py-3">
                    <Pagination links={attendances.links} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
