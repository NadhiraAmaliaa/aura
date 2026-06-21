import MaterialIcon from "@/Components/MaterialIcon";
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

const animationStyles = `
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  @keyframes slideInRight {
    from {
      opacity: 0;
      transform: translateX(-10px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  @keyframes scaleIn {
    from {
      opacity: 0;
      transform: scale(0.95);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  .card-fade-in {
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
  }
  .card-fade-in:nth-child(1) { animation-delay: 0.1s; }
  .card-fade-in:nth-child(2) { animation-delay: 0.2s; }
  .card-fade-in:nth-child(3) { animation-delay: 0.3s; }
  .card-fade-in:nth-child(4) { animation-delay: 0.4s; }
  .list-slide-in {
    animation: slideInRight 0.4s ease-out forwards;
    opacity: 0;
  }
  .section-scale-in {
    animation: scaleIn 0.5s ease-out forwards;
    opacity: 0;
  }
`;

const cards: {
    key: keyof Stats;
    label: string;
    icon: string;
    bgGradient: string;
    iconClass: string;
}[] = [
    {
        key: "total_interns",
        label: "Total Peserta",
        icon: "groups",
        bgGradient: "from-blue-900 to-blue-500",
        iconClass: "bg-white/20",
    },
    {
        key: "active_interns",
        label: "Peserta Aktif",
        icon: "how_to_reg",
        bgGradient: "from-blue-700 to-sky-400",
        iconClass: "bg-white/20",
    },
    {
        key: "present_today",
        label: "Hadir Hari Ini",
        icon: "fact_check",
        bgGradient: "from-blue-600 to-cyan-400",
        iconClass: "bg-white/20",
    },
    {
        key: "pending_leaves",
        label: "Izin Menunggu",
        icon: "pending_actions",
        bgGradient: "from-orange-600 to-yellow-400",
        iconClass: "bg-white/20",
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

            <style>{animationStyles}</style>

            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                {cards.map((card) => (
                    <div
                        key={card.key}
                        className={`card-fade-in relative overflow-hidden rounded-2xl bg-gradient-to-br ${card.bgGradient} p-6 text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105`}
                    >
                        <div className="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10"></div>
                        <div className="relative z-10">
                            <div className="flex items-start justify-between">
                                <div>
                                    <p className="text-sm font-medium text-white/80">
                                        {card.label}
                                    </p>
                                    <p className="mt-2 text-4xl font-bold text-white">
                                        {stats[card.key]}
                                    </p>
                                </div>
                                <span
                                    className={`flex h-14 w-14 items-center justify-center rounded-2xl ${card.iconClass} backdrop-blur-sm`}
                                >
                                    <MaterialIcon
                                        name={card.icon}
                                        style={{ fontSize: 28, color: "white" }}
                                    />
                                </span>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="section-scale-in mt-8 overflow-hidden rounded-2xl border border-outline-variant bg-white shadow-sm">
                <div className="border-b border-outline-variant bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                    <MaterialIcon
                                        name="mail_outline"
                                        style={{
                                            fontSize: 20,
                                            color: "#0066cc",
                                        }}
                                    />
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-on-surface">
                                        Pengajuan Izin Terbaru
                                    </h3>
                                    <p className="text-xs text-on-surface-variant">
                                        {recentLeaves.length === 0
                                            ? "Semua pengajuan telah diproses"
                                            : `${recentLeaves.length} pengajuan menunggu persetujuan`}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Link
                            href={route("admin.leave-requests.index")}
                            className="inline-flex h-10 items-center gap-1.5 rounded-lg bg-primary px-4 text-sm font-semibold text-white transition hover:bg-primary/90 active:scale-95"
                        >
                            <MaterialIcon
                                name="arrow_forward"
                                style={{ fontSize: 18 }}
                            />
                            Lihat Semua
                        </Link>
                    </div>
                </div>

                {recentLeaves.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-16 px-6">
                        <div className="rounded-full bg-gradient-to-br from-blue-100 to-blue-200 p-4 mb-4">
                            <MaterialIcon
                                name="inbox"
                                style={{
                                    fontSize: 40,
                                    color: "#0066cc",
                                }}
                            />
                        </div>
                        <h4 className="text-base font-semibold text-on-surface mb-1">
                            Tidak Ada Pengajuan Menunggu
                        </h4>
                        <p className="text-sm text-on-surface-variant text-center max-w-sm">
                            Semua pengajuan izin peserta magang telah diproses.
                            Kembali lagi nanti!
                        </p>
                    </div>
                ) : (
                    <div className="divide-y divide-outline-variant">
                        {recentLeaves.map((leave, index) => {
                            const isAmbilSakit = leave.type === "sakit";
                            const badgeColor = isAmbilSakit
                                ? "bg-red-100 text-red-700"
                                : "bg-blue-100 text-blue-700";
                            const iconColor = isAmbilSakit
                                ? "#dc2626"
                                : "#0066cc";

                            return (
                                <Link
                                    key={leave.id}
                                    href={route(
                                        "admin.leave-requests.show",
                                        leave.id,
                                    )}
                                    className={`list-slide-in block px-6 py-4 transition-all hover:bg-blue-50 active:bg-blue-100 group`}
                                    style={{
                                        animationDelay: `${index * 0.1 + 0.5}s`,
                                    }}
                                >
                                    <div className="flex items-center gap-4">
                                        <div
                                            className={`flex h-12 w-12 items-center justify-center rounded-lg ${
                                                isAmbilSakit
                                                    ? "bg-red-100"
                                                    : "bg-blue-100"
                                            }`}
                                        >
                                            <MaterialIcon
                                                name={
                                                    isAmbilSakit
                                                        ? "local_hospital"
                                                        : "description"
                                                }
                                                style={{
                                                    fontSize: 20,
                                                    color: iconColor,
                                                }}
                                            />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2 mb-1">
                                                <p className="font-semibold text-on-surface truncate">
                                                    {leave.user?.name}
                                                </p>
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${badgeColor}`}
                                                >
                                                    {
                                                        leaveTypeLabels[
                                                            leave.type
                                                        ]
                                                    }
                                                </span>
                                            </div>
                                            <p className="text-sm text-on-surface-variant flex items-center gap-1">
                                                <MaterialIcon
                                                    name="calendar_today"
                                                    style={{
                                                        fontSize: 14,
                                                        display: "inline",
                                                    }}
                                                />
                                                {formatDate(leave.start_date)} -{" "}
                                                {formatDate(leave.end_date)}
                                            </p>
                                        </div>
                                        <MaterialIcon
                                            name="chevron_right"
                                            style={{
                                                fontSize: 24,
                                                color: "#999",
                                                transition: "all 0.3s ease",
                                            }}
                                            className="group-hover:translate-x-1 transition-transform"
                                        />
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
