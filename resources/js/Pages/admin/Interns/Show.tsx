import StatusBadge from "@/Components/admin/StatusBadge";
import {
    formatDate,
    internStatusBadgeTone,
    internStatusLabels,
} from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Intern, PageProps } from "@/types";
import { Head, Link, usePage } from "@inertiajs/react";

function Row({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="grid grid-cols-3 gap-4 py-3">
            <dt className="text-sm text-on-surface-variant">{label}</dt>
            <dd className="col-span-2 text-sm font-medium text-on-surface">
                {value ?? "-"}
            </dd>
        </div>
    );
}

export default function Show({ intern }: { intern: Intern }) {
    const isAdmin = usePage<PageProps>().props.auth.user?.is_admin ?? false;
    const period =
        intern.start_date && intern.end_date
            ? `${formatDate(intern.start_date)} - ${formatDate(intern.end_date)}`
            : "-";

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between gap-6">
                    <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                        Detail Peserta Magang
                    </h1>
                    <div className="flex shrink-0 items-center gap-4">
                        {isAdmin && (
                            <Link
                                href={route("admin.interns.edit", intern.id)}
                                className="text-sm font-semibold text-primary hover:underline"
                            >
                                Ubah
                            </Link>
                        )}
                        <Link
                            href={route("admin.interns.index")}
                            className="text-sm font-semibold text-primary hover:underline"
                        >
                            Kembali
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Detail Peserta Magang" />

            <div className="space-y-6">
                <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-on-surface">
                            {intern.user?.name ?? "-"}
                        </h3>
                        <StatusBadge
                            tone={internStatusBadgeTone[intern.effective_status]}
                        >
                            {internStatusLabels[intern.effective_status]}
                        </StatusBadge>
                    </div>
                    <dl className="divide-y divide-outline-variant">
                        <Row label="Nama" value={intern.user?.name} />
                        <Row label="NIM" value={intern.nim} />
                        <Row label="Email" value={intern.user?.email} />
                        <Row label="Nomor Telepon" value={intern.phone} />
                    </dl>
                </div>

                <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                    <h3 className="mb-2 text-lg font-semibold text-on-surface">
                        Data Magang
                    </h3>
                    <dl className="divide-y divide-outline-variant">
                        <Row
                            label="Program Magang"
                            value={intern.intern_program?.name}
                        />
                        <Row
                            label="Perguruan Tinggi"
                            value={intern.university_ref?.name ?? intern.university}
                        />
                        <Row
                            label="Program Studi"
                            value={intern.study_program?.name ?? intern.major}
                        />
                        <Row
                            label="Divisi"
                            value={intern.division_ref?.name ?? intern.division}
                        />
                        <Row label="Periode Magang" value={period} />
                    </dl>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
