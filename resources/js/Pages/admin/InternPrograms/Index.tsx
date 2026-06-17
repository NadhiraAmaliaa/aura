import DangerButton from "@/Components/DangerButton";
import Modal from "@/Components/Modal";
import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { InternProgram, Paginated } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";

type ProgramRow = InternProgram & { interns_count: number };

export default function Index({
    programs,
}: {
    programs: Paginated<ProgramRow>;
}) {
    const [deleting, setDeleting] = useState<ProgramRow | null>(null);

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route("admin.intern-programs.destroy", deleting.id), {
            onFinish: () => setDeleting(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Program Magang
                    </h2>
                    <Link
                        href={route("admin.intern-programs.create")}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Program
                    </Link>
                </div>
            }
        >
            <Head title="Program Magang" />

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Deskripsi
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Peserta
                                </th>
                                <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {programs.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Belum ada program magang.
                                    </td>
                                </tr>
                            ) : (
                                programs.data.map((program) => (
                                    <tr key={program.id}>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {program.name}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600">
                                            {program.description ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {program.interns_count}
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    "admin.intern-programs.edit",
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
                    <Pagination links={programs.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus program magang ini?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Program {deleting?.name} akan dihapus permanen.
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
