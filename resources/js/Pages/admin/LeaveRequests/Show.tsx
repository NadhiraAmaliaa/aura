import StatusBadge from "@/Components/admin/StatusBadge";
import MaterialIcon from "@/Components/MaterialIcon";
import DangerButton from "@/Components/DangerButton";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextareaInput from "@/Components/TextareaInput";
import { formatDate, leaveStatusLabels, leaveTypeLabels } from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { LeaveRequest, LeaveStatus, PageProps } from "@/types";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { FormEventHandler } from "react";

const statusTone: Record<
    LeaveStatus,
    "success" | "neutral" | "info" | "warning" | "danger"
> = {
    pending: "warning",
    approved: "success",
    rejected: "danger",
};

function Row({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="grid grid-cols-3 gap-4 py-3">
            <dt className="text-sm text-on-surface-variant">{label}</dt>
            <dd className="col-span-2 text-sm font-medium text-on-surface">
                {value}
            </dd>
        </div>
    );
}

export default function Show({ leaveRequest }: { leaveRequest: LeaveRequest }) {
    const isSupervisor =
        usePage<PageProps>().props.auth.user?.is_supervisor ?? false;
    const isPending = leaveRequest.status === "pending";

    const { data, setData, patch, processing, errors } = useForm({
        admin_note: leaveRequest.admin_note ?? "",
    });

    const decide =
        (decision: "approve" | "reject"): FormEventHandler =>
        (event) => {
            event.preventDefault();
            patch(route(`admin.leave-requests.${decision}`, leaveRequest.id), {
                preserveScroll: true,
            });
        };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-6">
                    <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                        Detail Pengajuan
                    </h1>
                    <Link
                        href={route("admin.leave-requests.index")}
                        className="shrink-0 text-sm font-semibold text-primary hover:underline"
                    >
                        Kembali
                    </Link>
                </div>
            }
        >
            <Head title={`Pengajuan ${leaveRequest.request_number}`} />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2">
                    <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-on-surface">
                                {leaveRequest.request_number}
                            </h3>
                            <StatusBadge tone={statusTone[leaveRequest.status]}>
                                {leaveStatusLabels[leaveRequest.status]}
                            </StatusBadge>
                        </div>

                        <dl className="divide-y divide-outline-variant">
                            <Row label="Nama" value={leaveRequest.user?.name} />
                            <Row
                                label="NIM"
                                value={leaveRequest.user?.intern?.nim ?? "-"}
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
                                value={leaveRequest.reason ?? "-"}
                            />
                            <Row
                                label="Kontak"
                                value={leaveRequest.contact_phone ?? "-"}
                            />
                            <Row
                                label="Alamat"
                                value={leaveRequest.address ?? "-"}
                            />
                            <Row
                                label="Bukti"
                                value={
                                    leaveRequest.evidence_url ? (
                                        <a
                                            href={leaveRequest.evidence_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="font-medium text-primary hover:underline"
                                        >
                                            Lihat / Unduh Lampiran
                                        </a>
                                    ) : (
                                        "-"
                                    )
                                }
                            />
                        </dl>
                    </div>
                </div>

                <div>
                    <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-on-surface">
                            Keputusan
                        </h3>

                        {leaveRequest.status === "approved" && (
                            <a
                                href={route(
                                    "leave-requests.pdf",
                                    leaveRequest.id,
                                )}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mb-4 flex items-center justify-center gap-2 rounded-lg border border-primary bg-primary/5 px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary/10"
                            >
                                <MaterialIcon
                                    name="picture_as_pdf"
                                    style={{ fontSize: 18 }}
                                />
                                Cetak PDF Surat
                            </a>
                        )}

                        {isPending ? (
                            isSupervisor ? (
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
                                            onChange={(event) =>
                                                setData(
                                                    "admin_note",
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        {errors.admin_note && (
                                            <p className="mt-2 text-sm text-error">
                                                {errors.admin_note}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex gap-3">
                                        <PrimaryButton
                                            onClick={decide("approve")}
                                            disabled={processing}
                                        >
                                            Setujui
                                        </PrimaryButton>
                                        <DangerButton
                                            onClick={decide("reject")}
                                            disabled={processing}
                                        >
                                            Tolak
                                        </DangerButton>
                                    </div>
                                </form>
                            ) : (
                                <p className="text-sm text-on-surface-variant">
                                    Pengajuan ini masih menunggu keputusan
                                    mentor bagian.
                                </p>
                            )
                        ) : (
                            <div className="space-y-3 text-sm">
                                <p className="text-on-surface-variant">
                                    Pengajuan ini telah{" "}
                                    {leaveStatusLabels[
                                        leaveRequest.status
                                    ].toLowerCase()}
                                    .
                                </p>
                                {leaveRequest.admin_note && (
                                    <div>
                                        <p className="text-on-surface-variant">
                                            Catatan Admin
                                        </p>
                                        <p className="font-medium text-on-surface">
                                            {leaveRequest.admin_note}
                                        </p>
                                    </div>
                                )}
                                {leaveRequest.approver && (
                                    <div>
                                        <p className="text-on-surface-variant">
                                            Diproses oleh
                                        </p>
                                        <p className="font-medium text-on-surface">
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
