import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Badge from '@/Components/Badge';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import TextareaInput from '@/Components/TextareaInput';
import {
    formatDate,
    leaveStatusBadge,
    leaveStatusLabels,
    leaveTypeLabels,
} from '@/lib/labels';
import { LeaveRequest } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

function Row({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="grid grid-cols-3 gap-4 py-2">
            <dt className="text-sm text-gray-500">{label}</dt>
            <dd className="col-span-2 text-sm font-medium text-gray-900">
                {value}
            </dd>
        </div>
    );
}

export default function Show({
    leaveRequest,
}: {
    leaveRequest: LeaveRequest;
}) {
    const isPending = leaveRequest.status === 'pending';

    const { data, setData, patch, processing, errors } = useForm({
        admin_note: leaveRequest.admin_note ?? '',
    });

    const decide = (decision: 'approve' | 'reject'): FormEventHandler => (e) => {
        e.preventDefault();
        patch(route(`admin.leave-requests.${decision}`, leaveRequest.id), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Detail Pengajuan
                    </h2>
                    <Link
                        href={route('admin.leave-requests.index')}
                        className="text-sm font-medium text-green-700 hover:underline"
                    >
                        Kembali
                    </Link>
                </div>
            }
        >
            <Head title={`Pengajuan ${leaveRequest.request_number}`} />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-800">
                                {leaveRequest.request_number}
                            </h3>
                            <Badge
                                className={
                                    leaveStatusBadge[leaveRequest.status]
                                }
                            >
                                {leaveStatusLabels[leaveRequest.status]}
                            </Badge>
                        </div>

                        <dl className="divide-y divide-gray-100">
                            <Row
                                label="Nama"
                                value={leaveRequest.user?.name}
                            />
                            <Row
                                label="NIM"
                                value={leaveRequest.user?.intern?.nim ?? '-'}
                            />
                            <Row
                                label="Jenis"
                                value={leaveTypeLabels[leaveRequest.type]}
                            />
                            <Row
                                label="Periode"
                                value={`${formatDate(
                                    leaveRequest.start_date,
                                )} - ${formatDate(leaveRequest.end_date)}`}
                            />
                            <Row
                                label="Total Hari"
                                value={`${leaveRequest.total_days} hari`}
                            />
                            <Row
                                label="Alasan"
                                value={leaveRequest.reason ?? '-'}
                            />
                            <Row
                                label="Kontak"
                                value={leaveRequest.contact_phone ?? '-'}
                            />
                            <Row
                                label="Alamat"
                                value={leaveRequest.address ?? '-'}
                            />
                        </dl>
                    </div>
                </div>

                <div>
                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 text-lg font-semibold text-gray-800">
                            Keputusan
                        </h3>

                        {isPending ? (
                            <form className="space-y-4">
                                <div>
                                    <InputLabel
                                        htmlFor="admin_note"
                                        value="Catatan (opsional)"
                                    />
                                    <TextareaInput
                                        id="admin_note"
                                        rows={3}
                                        className="mt-1 block w-full"
                                        value={data.admin_note}
                                        onChange={(e) =>
                                            setData(
                                                'admin_note',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.admin_note && (
                                        <p className="mt-2 text-sm text-red-600">
                                            {errors.admin_note}
                                        </p>
                                    )}
                                </div>

                                <div className="flex gap-3">
                                    <PrimaryButton
                                        onClick={decide('approve')}
                                        disabled={processing}
                                    >
                                        Setujui
                                    </PrimaryButton>
                                    <DangerButton
                                        onClick={decide('reject')}
                                        disabled={processing}
                                    >
                                        Tolak
                                    </DangerButton>
                                </div>
                            </form>
                        ) : (
                            <div className="space-y-3 text-sm">
                                <p className="text-gray-600">
                                    Pengajuan ini telah{' '}
                                    {leaveStatusLabels[
                                        leaveRequest.status
                                    ].toLowerCase()}
                                    .
                                </p>
                                {leaveRequest.admin_note && (
                                    <div>
                                        <p className="text-gray-500">
                                            Catatan Admin
                                        </p>
                                        <p className="font-medium text-gray-900">
                                            {leaveRequest.admin_note}
                                        </p>
                                    </div>
                                )}
                                {leaveRequest.approver && (
                                    <div>
                                        <p className="text-gray-500">
                                            Diproses oleh
                                        </p>
                                        <p className="font-medium text-gray-900">
                                            {leaveRequest.approver.name}
                                        </p>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
