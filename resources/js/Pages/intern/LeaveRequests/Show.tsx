import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Badge from "@/Components/Badge";
import {
    formatDate,
    leaveStatusBadge,
    leaveStatusLabels,
    leaveTypeLabels,
} from "@/lib/labels";
import { LeaveRequest } from "@/types";
import { Head, Link } from "@inertiajs/react";
import { ReactNode } from "react";

function Row({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3">
            <dt className="text-sm font-medium text-gray-500">{label}</dt>
            <dd className="text-sm text-gray-900 sm:col-span-2">{children}</dd>
        </div>
    );
}

export default function Show({ leaveRequest }: { leaveRequest: LeaveRequest }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Detail Pengajuan Izin
                </h2>
            }
        >
            <Head title="Detail Pengajuan" />

            <div className="max-w-2xl rounded-lg bg-white p-6 shadow">
                <div className="mb-4 flex items-center justify-between">
                    <span className="font-mono text-sm text-gray-500">
                        {leaveRequest.request_number}
                    </span>
                    <Badge className={leaveStatusBadge[leaveRequest.status]}>
                        {leaveStatusLabels[leaveRequest.status]}
                    </Badge>
                </div>

                <dl className="divide-y divide-gray-100">
                    <div className="rounded-lg bg-green-50 px-4 py-3 -mx-6 mb-4 border-l-4 border-green-700">
                        <div className="grid grid-cols-1 gap-1 sm:grid-cols-3">
                            <dt className="text-xs font-semibold uppercase tracking-wider text-green-700">
                                Tanggal Pengajuan
                            </dt>
                            <dd className="text-base font-semibold text-gray-900 sm:col-span-2">
                                {formatDate(leaveRequest.created_at)}
                            </dd>
                        </div>
                    </div>
                    <Row label="Jenis">
                        {leaveTypeLabels[leaveRequest.type]}
                    </Row>
                    <Row label="Periode">
                        {formatDate(leaveRequest.start_date)} &ndash;{" "}
                        {formatDate(leaveRequest.end_date)} (
                        {leaveRequest.total_days} hari)
                    </Row>
                    <Row label="Alasan">{leaveRequest.reason}</Row>
                    {leaveRequest.contact_phone && (
                        <Row label="Nomor Kontak">
                            {leaveRequest.contact_phone}
                        </Row>
                    )}
                    {leaveRequest.address && (
                        <Row label="Alamat Selama Izin">
                            {leaveRequest.address}
                        </Row>
                    )}
                    {leaveRequest.evidence_url && (
                        <Row label="Bukti">
                            <a
                                href={leaveRequest.evidence_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="font-medium text-green-700 hover:underline"
                            >
                                Lihat / Unduh Lampiran
                            </a>
                        </Row>
                    )}
                    {leaveRequest.status !== "pending" && (
                        <Row label="Catatan Admin">
                            {leaveRequest.admin_note || "-"}
                        </Row>
                    )}
                    {leaveRequest.approver && (
                        <Row label="Diproses oleh">
                            {leaveRequest.approver.name}
                        </Row>
                    )}
                </dl>

                <div className="mt-6 flex items-center gap-3 border-t border-gray-100 pt-4">
                    {leaveRequest.status === "approved" && (
                        <a
                            href={route("leave-requests.pdf", leaveRequest.id)}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="rounded-md bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                        >
                            Unduh Surat (PDF)
                        </a>
                    )}
                    <Link
                        href={route("intern.leave-requests.index")}
                        className="text-sm font-medium text-gray-600 hover:underline"
                    >
                        Kembali
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
