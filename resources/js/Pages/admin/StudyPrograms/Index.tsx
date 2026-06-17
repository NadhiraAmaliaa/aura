import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Badge from "@/Components/Badge";
import DangerButton from "@/Components/DangerButton";
import Modal from "@/Components/Modal";
import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Paginated, StudyProgram, University } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

type StudyProgramRow = StudyProgram & {
    interns_count: number;
    university?: University | null;
};

interface Filters {
    search: string;
    status: string;
    university_id: number | null;
}

export default function Index({
    studyPrograms,
    selectedUniversity,
    filters,
}: {
    studyPrograms: Paginated<StudyProgramRow>;
    selectedUniversity: University | null;
    filters: Filters;
}) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [universityId, setUniversityId] = useState<number | string>(
        filters.university_id ?? "",
    );
    const [deleting, setDeleting] = useState<StudyProgramRow | null>(null);

    const applyFilters = (next: {
        search?: string;
        status?: string;
        university_id?: number | string;
    }) => {
        router.get(
            route("admin.study-programs.index"),
            {
                search: next.search ?? search,
                status: next.status ?? status,
                university_id: next.university_id ?? universityId,
            },
            { preserveState: true, replace: true },
        );
    };

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();
        applyFilters({});
    };

    const handleUniversityFilter = (option: AutocompleteOption | null) => {
        const next = option ? option.id : "";
        setUniversityId(next);
        applyFilters({ university_id: next });
    };

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route("admin.study-programs.destroy", deleting.id), {
            onFinish: () => setDeleting(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Program Studi
                    </h2>
                    <Link
                        href={route("admin.study-programs.create", {
                            university_id: universityId || undefined,
                        })}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Program Studi
                    </Link>
                </div>
            }
        >
            <Head title="Program Studi" />

            <div className="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label className="block text-xs font-medium text-gray-500">
                        Perguruan Tinggi
                    </label>
                    <div className="mt-1">
                        <Autocomplete
                            url={route("lookup.universities")}
                            value={universityId}
                            displayValue={selectedUniversity?.name ?? ""}
                            placeholder="Semua / cari perguruan tinggi..."
                            onSelect={handleUniversityFilter}
                        />
                    </div>
                </div>
                <form onSubmit={submitSearch} className="sm:col-span-1">
                    <label className="block text-xs font-medium text-gray-500">
                        Cari nama program studi
                    </label>
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Ketik lalu tekan Enter..."
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600"
                    />
                </form>
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
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jenjang
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Perguruan Tinggi
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
                            {studyPrograms.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Tidak ada program studi yang cocok.
                                    </td>
                                </tr>
                            ) : (
                                studyPrograms.data.map((program) => (
                                    <tr key={program.id}>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {program.name}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-500">
                                            {program.level ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {program.university?.name ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {program.interns_count}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    program.is_active
                                                        ? "bg-green-100 text-green-800"
                                                        : "bg-gray-100 text-gray-800"
                                                }
                                            >
                                                {program.is_active
                                                    ? "Aktif"
                                                    : "Nonaktif"}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    "admin.study-programs.edit",
                                                    program.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Ubah
                                            </Link>
                                            <button
                                                onClick={() =>
                                                    setDeleting(program)
                                                }
                                                disabled={
                                                    program.interns_count > 0
                                                }
                                                title={
                                                    program.interns_count > 0
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
                    <Pagination links={studyPrograms.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus program studi ini?
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
