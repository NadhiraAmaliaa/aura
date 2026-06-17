import Badge from "@/Components/Badge";
import DangerButton from "@/Components/DangerButton";
import Modal from "@/Components/Modal";
import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
    formatDate,
    internStatusBadge,
    internStatusLabels,
} from "@/lib/labels";
import { Division, Intern, InternProgram, Paginated } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

interface Filters {
    search: string;
    program: number | null;
    division: number | null;
    status: string;
    period_from: string | null;
    period_to: string | null;
}

export default function Index({
    interns,
    programs,
    divisions,
    filters,
}: {
    interns: Paginated<Intern>;
    programs: Pick<InternProgram, "id" | "name">[];
    divisions: Pick<Division, "id" | "name">[];
    filters: Filters;
}) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [program, setProgram] = useState(
        filters.program ? String(filters.program) : "",
    );
    const [division, setDivision] = useState(
        filters.division ? String(filters.division) : "",
    );
    const [status, setStatus] = useState(filters.status ?? "");
    const [periodFrom, setPeriodFrom] = useState(filters.period_from ?? "");
    const [periodTo, setPeriodTo] = useState(filters.period_to ?? "");
    const [deleting, setDeleting] = useState<Intern | null>(null);

    const applyFilters = (next: Partial<Record<string, string>>) => {
        router.get(
            route("admin.interns.index"),
            {
                search: next.search ?? search,
                program: next.program ?? program,
                division: next.division ?? division,
                status: next.status ?? status,
                period_from: next.period_from ?? periodFrom,
                period_to: next.period_to ?? periodTo,
            },
            { preserveState: true, replace: true },
        );
    };

    const submitFilters: FormEventHandler = (e) => {
        e.preventDefault();
        applyFilters({});
    };

    const resetFilters = () => {
        setSearch("");
        setProgram("");
        setDivision("");
        setStatus("");
        setPeriodFrom("");
        setPeriodTo("");
        router.get(
            route("admin.interns.index"),
            {},
            { preserveState: true, replace: true },
        );
    };

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route("admin.interns.destroy", deleting.id), {
            onFinish: () => setDeleting(null),
        });
    };

    const periodText = (intern: Intern) => {
        if (!intern.start_date && !intern.end_date) {
            return "-";
        }

        return `${formatDate(intern.start_date)} - ${formatDate(intern.end_date)}`;
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Peserta Magang
                    </h2>
                    <Link
                        href={route("admin.interns.create")}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Peserta
                    </Link>
                </div>
            }
        >
            <Head title="Peserta Magang" />

            <form
                onSubmit={submitFilters}
                className="mb-4 rounded-lg bg-white p-4 shadow"
            >
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label className="block text-xs font-medium text-gray-500">
                            Cari nama atau NIM
                        </label>
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Ketik lalu tekan Enter..."
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500">
                            Program Magang
                        </label>
                        <select
                            value={program}
                            onChange={(e) => {
                                setProgram(e.target.value);
                                applyFilters({ program: e.target.value });
                            }}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                        >
                            <option value="">Semua program</option>
                            {programs.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500">
                            Divisi
                        </label>
                        <select
                            value={division}
                            onChange={(e) => {
                                setDivision(e.target.value);
                                applyFilters({ division: e.target.value });
                            }}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                        >
                            <option value="">Semua divisi</option>
                            {divisions.map((d) => (
                                <option key={d.id} value={d.id}>
                                    {d.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500">
                            Status
                        </label>
                        <select
                            value={status}
                            onChange={(e) => {
                                setStatus(e.target.value);
                                applyFilters({ status: e.target.value });
                            }}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                        >
                            <option value="">Semua status</option>
                            {Object.entries(internStatusLabels).map(
                                ([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500">
                            Periode mulai dari
                        </label>
                        <input
                            type="date"
                            value={periodFrom}
                            onChange={(e) => {
                                setPeriodFrom(e.target.value);
                                applyFilters({ period_from: e.target.value });
                            }}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-500">
                            Periode sampai
                        </label>
                        <input
                            type="date"
                            value={periodTo}
                            onChange={(e) => {
                                setPeriodTo(e.target.value);
                                applyFilters({ period_to: e.target.value });
                            }}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                        />
                    </div>
                </div>
                <div className="mt-3 flex items-center gap-3">
                    <button
                        type="submit"
                        className="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900"
                    >
                        Terapkan
                    </button>
                    <button
                        type="button"
                        onClick={resetFilters}
                        className="text-sm font-medium text-gray-500 hover:text-gray-700"
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
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    NIM
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Perguruan Tinggi
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Program Studi
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Divisi
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Program Magang
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Periode Magang
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
                            {interns.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={9}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Tidak ada peserta magang yang cocok.
                                    </td>
                                </tr>
                            ) : (
                                interns.data.map((intern) => (
                                    <tr key={intern.id}>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {intern.user?.name}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.nim ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.university_ref?.name ??
                                                intern.university ??
                                                "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.study_program?.name ??
                                                intern.major ??
                                                "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.division_ref?.name ??
                                                intern.division ??
                                                "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.intern_program?.name ?? "-"}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                            {periodText(intern)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    internStatusBadge[
                                                        intern.status
                                                    ]
                                                }
                                            >
                                                {
                                                    internStatusLabels[
                                                        intern.status
                                                    ]
                                                }
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    "admin.interns.edit",
                                                    intern.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Ubah
                                            </Link>
                                            <button
                                                onClick={() =>
                                                    setDeleting(intern)
                                                }
                                                className="ms-4 font-medium text-red-600 hover:underline"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="border-t border-gray-100 px-4 py-3">
                    <Pagination links={interns.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus peserta magang ini?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Akun pengguna dan seluruh data absensi{" "}
                        {deleting?.user?.name} akan dihapus permanen.
                    </p>
                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={() => setDeleting(null)}>
                            Batal
                        </SecondaryButton>
                        <DangerButton className="ms-3" onClick={confirmDelete}>
                            Hapus
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
