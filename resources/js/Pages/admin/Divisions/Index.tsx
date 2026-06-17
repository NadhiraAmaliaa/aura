import Badge from '@/Components/Badge';
import DangerButton from '@/Components/DangerButton';
import Modal from '@/Components/Modal';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Division, Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

type DivisionRow = Division & { interns_count: number };

export default function Index({
    divisions,
}: {
    divisions: Paginated<DivisionRow>;
}) {
    const [deleting, setDeleting] = useState<DivisionRow | null>(null);

    const confirmDelete = () => {
        if (!deleting) {
            return;
        }

        router.delete(route('admin.divisions.destroy', deleting.id), {
            onFinish: () => setDeleting(null),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Divisi
                    </h2>
                    <Link
                        href={route('admin.divisions.create')}
                        className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                    >
                        Tambah Divisi
                    </Link>
                </div>
            }
        >
            <Head title="Divisi" />

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Nama
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
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
                            {divisions.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        Belum ada divisi.
                                    </td>
                                </tr>
                            ) : (
                                divisions.data.map((division) => (
                                    <tr key={division.id}>
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {division.name}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                className={
                                                    division.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-gray-100 text-gray-800'
                                                }
                                            >
                                                {division.is_active
                                                    ? 'Aktif'
                                                    : 'Nonaktif'}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-700">
                                            {division.interns_count}
                                        </td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link
                                                href={route(
                                                    'admin.divisions.edit',
                                                    division.id,
                                                )}
                                                className="font-medium text-green-700 hover:underline"
                                            >
                                                Ubah
                                            </Link>
                                            <button
                                                onClick={() =>
                                                    setDeleting(division)
                                                }
                                                disabled={
                                                    division.interns_count > 0
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
                    <Pagination links={divisions.links} />
                </div>
            </div>

            <Modal show={deleting !== null} onClose={() => setDeleting(null)}>
                <div className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        Hapus divisi ini?
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Divisi {deleting?.name} akan dihapus permanen.
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
