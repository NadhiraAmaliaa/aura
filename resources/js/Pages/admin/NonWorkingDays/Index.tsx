import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDate, nonWorkingDayTypeLabels } from '@/lib/labels';
import { NonWorkingDay, Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({
    nonWorkingDays,
}: {
    nonWorkingDays: Paginated<NonWorkingDay>;
}) {
    const [deleting, setDeleting] = useState<NonWorkingDay | null>(null);

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route('admin.non-working-days.destroy', deleting.id), {
            onFinish: () => setDeleting(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Hari Libur
                    </h2>
                    <Link
                        href={route('admin.non-working-days.create')}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Hari Libur
                    </Link>
                </div>
            }
        >
            <Head title="Hari Libur" />

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
                                    Jenis
                                </th>
                                <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {nonWorkingDays.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Belum ada hari libur terdaftar.
                                    </td>
                                </tr>
                            ) : (
                                nonWorkingDays.data.map((day) => (
                                    <tr key={day.id}>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {formatDate(day.date)}
                                        </td>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {day.name}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge className="bg-gray-100 text-gray-700">
                                                {
                                                    nonWorkingDayTypeLabels[
                                                        day.type
                                                    ]
                                                }
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    'admin.non-working-days.edit',
                                                    day.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Ubah
                                            </Link>
                                            <button
                                                onClick={() =>
                                                    setDeleting(day)
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
                    <Pagination links={nonWorkingDays.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus hari libur ini?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        {deleting?.name} ({formatDate(deleting?.date)}) akan
                        dihapus.
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
