import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import { formatDate } from '@/lib/labels';
import { InternProgram, Recap } from '@/types';
import { Head, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Filters {
    start_date: string;
    end_date: string;
    program: number | null;
}

export default function RecapPage({
    recap,
    programs,
    filters,
}: {
    recap: Recap;
    programs: InternProgram[];
    filters: Filters;
}) {
    const [form, setForm] = useState({
        start_date: filters.start_date,
        end_date: filters.end_date,
        program: filters.program ? String(filters.program) : '',
    });

    const applyFilters: FormEventHandler = (e) => {
        e.preventDefault();
        router.get(route('admin.attendances.recap'), form, {
            preserveState: true,
            replace: true,
        });
    };

    const exportUrl = (type: 'excel' | 'pdf') =>
        route(`admin.attendances.recap.${type}`, form);

    const columns: { key: keyof Recap['rows'][number]; label: string }[] = [
        { key: 'effective_working_days', label: 'HKE' },
        { key: 'hadir', label: 'Hadir' },
        { key: 'wfo', label: 'WFO' },
        { key: 'wfh', label: 'WFH' },
        { key: 'dinas', label: 'Dinas' },
        { key: 'izin', label: 'Izin' },
        { key: 'tidak_absen', label: 'Tidak Absen' },
        { key: 'terlambat', label: 'Terlambat' },
        { key: 'tidak_co', label: 'Tidak CO' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Rekap Absensi
                </h2>
            }
        >
            <Head title="Rekap Absensi" />

            <form
                onSubmit={applyFilters}
                className="mb-4 grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-2 lg:grid-cols-4"
            >
                <div>
                    <label className="mb-1 block text-xs font-medium text-gray-500">
                        Tanggal Awal
                    </label>
                    <TextInput
                        type="date"
                        className="w-full"
                        value={form.start_date}
                        onChange={(e) =>
                            setForm({ ...form, start_date: e.target.value })
                        }
                    />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-medium text-gray-500">
                        Tanggal Akhir
                    </label>
                    <TextInput
                        type="date"
                        className="w-full"
                        value={form.end_date}
                        onChange={(e) =>
                            setForm({ ...form, end_date: e.target.value })
                        }
                    />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-medium text-gray-500">
                        Program
                    </label>
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
                </div>
                <div className="flex items-end gap-2">
                    <button
                        type="submit"
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Terapkan
                    </button>
                    <a
                        href={exportUrl('excel')}
                        className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Excel
                    </a>
                    <a
                        href={exportUrl('pdf')}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        PDF
                    </a>
                </div>
            </form>

            <div className="mb-3 text-sm text-gray-600">
                Periode {formatDate(recap.start_date)} -{' '}
                {formatDate(recap.end_date)} &middot; Hari Kerja Efektif:{' '}
                <span className="font-semibold">
                    {recap.effective_working_days}
                </span>
            </div>

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                {columns.map((col) => (
                                    <th
                                        key={col.key}
                                        className="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        {col.label}
                                    </th>
                                ))}
                                <th className="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    % Terlambat
                                </th>
                                <th className="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                    % Tidak Absen
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {recap.rows.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={columns.length + 3}
                                        className="px-4 py-8 text-center text-gray-500"
                                    >
                                        Tidak ada data untuk periode ini.
                                    </td>
                                </tr>
                            ) : (
                                recap.rows.map((row) => (
                                    <tr key={row.intern.id}>
                                        <td className="px-3 py-3">
                                            <div className="font-medium text-gray-900">
                                                {row.intern.user?.name}
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                {row.intern.nim}
                                            </div>
                                        </td>
                                        {columns.map((col) => (
                                            <td
                                                key={col.key}
                                                className="px-3 py-3 text-center text-gray-700"
                                            >
                                                {row[col.key] as number}
                                            </td>
                                        ))}
                                        <td className="px-3 py-3 text-center text-gray-700">
                                            {row.persen_terlambat}%
                                        </td>
                                        <td className="px-3 py-3 text-center text-gray-700">
                                            {row.persen_tidak_absen}%
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
