import Badge from "@/Components/Badge";
import DangerButton from "@/Components/DangerButton";
import Modal from "@/Components/Modal";
import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Paginated, University } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

type UniversityRow = University & {
    interns_count: number;
    study_programs_count: number;
};

interface Filters {
    search: string;
    status: string;
}

export default function Index({
    universities,
    filters,
}: {
    universities: Paginated<UniversityRow>;
    filters: Filters;
}) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [deleting, setDeleting] = useState<UniversityRow | null>(null);

    const applyFilters = (next: Partial<Filters>) => {
        router.get(
            route("admin.universities.index"),
            {
                search: next.search ?? search,
                status: next.status ?? status,
            },
            { preserveState: true, replace: true },
        );
    };

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();
        applyFilters({});
    };

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route("admin.universities.destroy", deleting.id), {
            onFinish: () => setDeleting(null),
        });
    };

    const isUsed = (university: UniversityRow) =>
        university.interns_count > 0 || university.study_programs_count > 0;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Perguruan Tinggi
                    </h2>
                    <Link
                        href={route("admin.universities.create")}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Perguruan Tinggi
                    </Link>
                </div>
            }
        >
            <Head title="Perguruan Tinggi" />

            <form
                onSubmit={submitSearch}
                className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end"
            >
                <div className="flex-1">
                    <label className="block text-xs font-medium text-gray-500">
                        Cari nama perguruan tinggi
                    </label>
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Ketik nama lalu tekan Enter..."
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                    />
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
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:w-40"
                    >
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <button
                    type="submit"
                    className="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900"
                >
                    Cari
                </button>
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
                                    LLDikti
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Prodi
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Peserta
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
                            {universities.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Tidak ada perguruan tinggi yang cocok.
                                    </td>
                                </tr>
                            ) : (
                                universities.data.map((university) => (
                                    <tr key={university.id}>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {university.name}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-500">
                                            {university.lldikti ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            <Link
                                                href={route(
                                                    "admin.study-programs.index",
                                                    {
                                                        university_id:
                                                            university.id,
                                                    },
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                {university.study_programs_count}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {university.interns_count}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    university.is_active
                                                        ? "bg-green-100 text-green-800"
                                                        : "bg-gray-100 text-gray-800"
                                                }
                                            >
                                                {university.is_active
                                                    ? "Aktif"
                                                    : "Nonaktif"}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    "admin.universities.edit",
                                                    university.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Ubah
                                            </Link>
                                            <button
                                                onClick={() =>
                                                    setDeleting(university)
                                                }
                                                disabled={isUsed(university)}
                                                title={
                                                    isUsed(university)
                                                        ? "Masih digunakan. Nonaktifkan saja."
                                                        : undefined
                                                }
                                                className="ms-4 font-medium text-red-600 hover:underline disabled:cursor-not-allowed disabled:text-gray-300 disabled:no-underline"
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
                    <Pagination links={universities.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus perguruan tinggi ini?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        {deleting?.name} akan dihapus permanen.
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
