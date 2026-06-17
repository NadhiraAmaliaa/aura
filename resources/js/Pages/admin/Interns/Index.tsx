import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Badge from "@/Components/Badge";
import DangerButton from "@/Components/DangerButton";
import Modal from "@/Components/Modal";
import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import { internStatusBadge, internStatusLabels } from "@/lib/labels";
import { Intern, Paginated } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";

export default function Index({ interns }: { interns: Paginated<Intern> }) {
    const [deleting, setDeleting] = useState<Intern | null>(null);

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route("admin.interns.destroy", deleting.id), {
            onFinish: () => setDeleting(null),
        });
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
                                    Program
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Divisi
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
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Belum ada peserta magang.
                                    </td>
                                </tr>
                            ) : (
                                interns.data.map((intern) => (
                                    <tr key={intern.id}>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-gray-900">
                                                {intern.user?.name}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {intern.university_ref?.name ??
                                                    intern.university ??
                                                    "-"}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.nim}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.intern_program?.name ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {intern.division}
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
