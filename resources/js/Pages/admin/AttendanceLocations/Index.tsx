import Badge from "@/Components/Badge";
import DangerButton from "@/Components/DangerButton";
import Modal from "@/Components/Modal";
import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { AttendanceLocation, Paginated } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";

export default function Index({
    locations,
}: {
    locations: Paginated<AttendanceLocation>;
}) {
    const [deleting, setDeleting] = useState<AttendanceLocation | null>(null);

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(
            route("admin.attendance-locations.destroy", deleting.id),
            {
                onFinish: () => setDeleting(null),
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Lokasi Absensi
                    </h2>
                    <Link
                        href={route("admin.attendance-locations.create")}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Lokasi
                    </Link>
                </div>
            }
        >
            <Head title="Lokasi Absensi" />

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Latitude
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Longitude
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Radius (m)
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
                            {locations.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Belum ada lokasi absensi.
                                    </td>
                                </tr>
                            ) : (
                                locations.data.map((location) => (
                                    <tr key={location.id}>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {location.name}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {location.latitude}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {location.longitude}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {location.radius}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    location.is_active
                                                        ? "bg-green-100 text-green-800"
                                                        : "bg-gray-100 text-gray-800"
                                                }
                                            >
                                                {location.is_active
                                                    ? "Aktif"
                                                    : "Nonaktif"}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    "admin.attendance-locations.edit",
                                                    location.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Ubah
                                            </Link>
                                            <button
                                                onClick={() =>
                                                    setDeleting(location)
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
                    <Pagination links={locations.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus lokasi ini?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Lokasi {deleting?.name} akan dihapus permanen.
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
