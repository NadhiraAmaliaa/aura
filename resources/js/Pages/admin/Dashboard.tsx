import MaterialIcon from "@/Components/MaterialIcon";
import StatusBadge from "@/Components/admin/StatusBadge";
import { formatDate, leaveTypeLabels } from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { LeaveRequest } from "@/types";
import { Head, Link } from "@inertiajs/react";

interface Stats {
    total_interns: number;
    active_interns: number;
    present_today: number;
    pending_leaves: number;
}

const cards: {
    key: keyof Stats;
    label: string;
    icon: string;
    iconClass: string;
}[] = [
    {
        key: "total_interns",
        label: "Total Peserta",
        icon: "groups",
        iconClass: "bg-primary/10 text-primary",
    },
    {
        key: "active_interns",
        label: "Peserta Aktif",
        icon: "how_to_reg",
        iconClass: "bg-tertiary/10 text-tertiary",
    },
    {
        key: "present_today",
        label: "Hadir Hari Ini",
        icon: "fact_check",
        iconClass: "bg-green-100 text-green-700",
    },
    {
        key: "pending_leaves",
        label: "Izin Menunggu",
        icon: "pending_actions",
        iconClass: "bg-amber-100 text-amber-700",
    },
];

export default function Dashboard({
    stats,
    recentLeaves,
}: {
    stats: Stats;
    recentLeaves: LeaveRequest[];
}) {
    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Dashboard
                </h1>
            }
        >
            <Head title="Dashboard" />

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {cards.map((card) => (
                    <div
                        key={card.key}
                        className="flex items-center gap-4 rounded-xl border border-outline-variant bg-white p-5 shadow-sm"
                    >
                        <span
                            className={`flex h-12 w-12 items-center justify-center rounded-xl ${card.iconClass}`}
                        >
                            <MaterialIcon name={card.icon} />
                        </span>
                        <div>
                            <p className="text-sm text-on-surface-variant">
                                {card.label}
                            </p>
                            <p className="mt-0.5 text-3xl font-bold text-on-surface">
                                {stats[card.key]}
                            </p>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-6 rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                <div className="mb-4 flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-on-surface">
                        Pengajuan Izin Terbaru
                    </h3>
                    <Link
                        href={route("admin.leave-requests.index")}
                        className="text-sm font-semibold text-primary hover:underline"
                    >
                        Lihat semua
                    </Link>
                </div>

                {recentLeaves.length === 0 ? (
                    <p className="text-sm text-on-surface-variant">
                        Tidak ada pengajuan yang menunggu.
                    </p>
                ) : (
                    <ul className="divide-y divide-outline-variant">
                        {recentLeaves.map((leave) => (
                            <li
                                key={leave.id}
                                className="flex items-center justify-between py-3"
                            >
                                <div>
                                    <Link
                                        href={route(
                                            "admin.leave-requests.show",
                                            leave.id,
                                        )}
                                        className="font-medium text-on-surface hover:underline"
                                    >
                                        {leave.user?.name}
                                    </Link>
                                    <p className="text-sm text-on-surface-variant">
                                        {formatDate(leave.start_date)} -{" "}
                                        {formatDate(leave.end_date)}
                                    </p>
                                </div>
                                <StatusBadge tone="info">
                                    {leaveTypeLabels[leave.type]}
                                </StatusBadge>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
