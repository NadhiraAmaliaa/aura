import DatePicker from "@/Components/admin/DatePicker";
import FilterCard, { FilterField } from "@/Components/admin/FilterCard";
import FilterSelect, {
    FilterSelectOption,
} from "@/Components/admin/FilterSelect";
import MaterialIcon from "@/Components/MaterialIcon";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { formatDate } from "@/lib/labels";
import {
    AttendanceReport,
    AttendanceReportCategory,
    AttendanceReportFilters,
    AttendanceReportRow,
    Division,
    InternProgram,
    PageProps,
} from "@/types";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, lazy, Suspense, useMemo, useState } from "react";

const AttendanceChart = lazy(() => import("@/Components/AttendanceChart"));

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

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes progressBarFill {
        from {
            width: 0% !important;
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
    .card-fade-in:nth-child(5) { animation-delay: 0.5s; }

    .icon-float {
        animation: float 3s ease-in-out infinite;
    }

    .progress-bar-fill {
        animation: progressBarFill 1s ease-out 0.3s forwards;
        width: 0% !important;
    }
`;

interface ReportPageProps {
    report: AttendanceReport;
    programs: InternProgram[];
    divisions: Division[];
    filters: AttendanceReportFilters;
}

const categoryBadge: Record<AttendanceReportCategory, string> = {
    wfo: "bg-green-100 text-green-800",
    wfh: "bg-sky-100 text-sky-800",
    dinas: "bg-purple-100 text-purple-800",
    izin: "bg-indigo-100 text-indigo-800",
    sakit: "bg-blue-100 text-blue-800",
    alpha: "bg-red-100 text-red-800",
    tidak_absen: "bg-gray-100 text-gray-700",
};

function dash(value: string | number | null | undefined): string {
    if (value === null || value === undefined || value === "") return "-";
    return String(value);
}

function SummaryCard({
    label,
    value,
    total = 0,
    icon,
    bgGradient,
}: {
    label: string;
    value: number;
    total?: number;
    icon: string;
    bgGradient: string;
}) {
    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
    const showBar = total > 0;
    return (
        <div
            className={`relative overflow-hidden rounded-2xl px-5 py-4 text-white shadow-lg transition-all duration-300 hover:shadow-2xl hover:scale-105 ${
                bgGradient
            } before:absolute before:right-0 before:top-1/2 before:-translate-y-1/2 before:text-white before:opacity-20 before:-mr-4`}
        >
            {/* Background Icon (large, semi-transparent, animated float) */}
            <div className="absolute right-0 top-4 text-white opacity-20 icon-float">
                <MaterialIcon name={icon} filled style={{ fontSize: 120 }} />
            </div>

            {/* Content */}
            <div className="relative z-10">
                <p className="truncate text-xs font-semibold uppercase tracking-wider text-white/80">
                    {label}
                </p>
                <p className="mt-2 text-4xl font-extrabold tabular-nums text-white">
                    {value}
                </p>
                <div className="mt-4 space-y-1.5 h-10">
                    {showBar ? (
                        <>
                            <div className="h-1.5 w-full overflow-hidden rounded-full bg-white/30">
                                <div
                                    className="h-full rounded-full bg-white/70 progress-bar-fill transition-all duration-1000"
                                    style={{ width: `${Math.min(pct, 100)}%` }}
                                />
                            </div>
                            <p className="text-xs text-white/70">
                                {pct}% dari total
                            </p>
                        </>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

export default function Report({
    report,
    programs,
    divisions,
    filters,
}: ReportPageProps) {
    const isAdmin = usePage<PageProps>().props.auth.user?.is_admin ?? false;
    const { data, setData, get } = useForm({
        start_date: filters.start_date,
        end_date: filters.end_date,
        program: filters.program ? String(filters.program) : "",
        division: filters.division ? String(filters.division) : "",
    });

    const programOptions: FilterSelectOption[] = programs.map((p) => ({
        value: String(p.id),
        label: p.name,
    }));

    const divisionOptions: FilterSelectOption[] = divisions.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        get(route("admin.attendances.index"), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const reset = () => {
        router.get(
            route("admin.attendances.index"),
            {},
            { preserveScroll: true },
        );
    };

    const [tableSearch, setTableSearch] = useState("");
    const [tablePerPage, setTablePerPage] = useState<number | null>(null);
    const [tablePage, setTablePage] = useState(1);

    const effectivePerPage = tablePerPage ?? 10;

    const exportUrl = route("admin.attendances.export", {
        start_date: data.start_date,
        end_date: data.end_date,
        program: data.program || undefined,
        division: data.division || undefined,
        search: tableSearch.trim() || undefined,
    });

    const filteredRows = useMemo(() => {
        const term = tableSearch.trim().toLowerCase();
        if (!term) return report.rows;
        return report.rows.filter((row) =>
            [
                row.nim,
                row.nama,
                row.program,
                row.divisi,
                row.hari,
                row.jenis_absen,
                row.check_in,
                row.check_out,
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [report.rows, tableSearch]);

    const totalFiltered = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalFiltered / effectivePerPage));
    const safePage = Math.min(tablePage, totalPages);
    const from =
        totalFiltered === 0 ? 0 : (safePage - 1) * effectivePerPage + 1;
    const to = Math.min(safePage * effectivePerPage, totalFiltered);
    const pagedRows = filteredRows.slice(from - 1, to);

    const handleSearchChange = (value: string) => {
        setTableSearch(value);
        setTablePage(1);
    };

    const handlePerPageChange = (value: number | null) => {
        setTablePerPage(value);
        setTablePage(1);
    };

    const isSameDay = report.start_date === report.end_date;
    const periodLabel = isSameDay
        ? formatDate(report.start_date)
        : `${formatDate(report.start_date)} — ${formatDate(report.end_date)}`;

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Reporting Absensi
                </h1>
            }
        >
            <Head title="Reporting Absensi" />
            <style>{animationStyles}</style>

            <div className="space-y-6">
                {/* 1. Filter Section */}
                <FilterCard
                    onSubmit={submit}
                    actions={
                        <>
                            <button
                                type="submit"
                                className="inline-flex h-10 items-center gap-1.5 rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary/90"
                            >
                                <MaterialIcon
                                    name="search"
                                    style={{ fontSize: 18 }}
                                />
                                Filter
                            </button>
                            <button
                                type="button"
                                onClick={reset}
                                className="inline-flex h-10 items-center gap-1.5 rounded-lg border border-outline-variant px-4 text-sm font-medium text-on-surface-variant transition hover:border-primary/50 hover:text-on-surface"
                            >
                                <MaterialIcon
                                    name="restart_alt"
                                    style={{ fontSize: 18 }}
                                />
                                Reset
                            </button>
                        </>
                    }
                >
                    <FilterField label="Tanggal Awal" htmlFor="start_date">
                        <DatePicker
                            id="start_date"
                            value={data.start_date}
                            onChange={(val) => setData("start_date", val)}
                        />
                    </FilterField>

                    <FilterField label="Tanggal Akhir" htmlFor="end_date">
                        <DatePicker
                            id="end_date"
                            value={data.end_date}
                            min={data.start_date}
                            onChange={(val) => setData("end_date", val)}
                        />
                    </FilterField>

                    <FilterField label="Program Magang" htmlFor="program">
                        <FilterSelect
                            id="program"
                            value={data.program}
                            options={programOptions}
                            placeholder="Semua program"
                            onChange={(val) => setData("program", val)}
                        />
                    </FilterField>

                    {isAdmin && (
                        <FilterField label="Divisi" htmlFor="division">
                            <FilterSelect
                                id="division"
                                value={data.division}
                                options={divisionOptions}
                                placeholder="Semua divisi"
                                onChange={(val) => setData("division", val)}
                            />
                        </FilterField>
                    )}
                </FilterCard>

                {/* Period Info & Export */}
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-outline-variant bg-white p-4 shadow-sm">
                    <p className="text-sm text-on-surface-variant">
                        Periode:{" "}
                        <span className="font-medium text-on-surface">
                            {periodLabel}
                        </span>
                        <span className="ml-2 text-on-surface-variant/70">
                            ({report.rows.length} baris)
                        </span>
                    </p>
                    <a
                        href={exportUrl}
                        className="inline-flex items-center gap-2 rounded-lg border border-transparent bg-[#28a745] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-[#22963e] focus:outline-none focus:ring-2 focus:ring-[#28a745] focus:ring-offset-2"
                    >
                        Export Excel
                    </a>
                </div>

                {/* 2. Summary Cards */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <div className="card-fade-in">
                        <SummaryCard
                            label="Total Peserta"
                            value={report.summary.total_peserta}
                            icon="group"
                            bgGradient="bg-gradient-to-br from-purple-300 to-purple-500"
                        />
                    </div>
                    <div className="card-fade-in">
                        <SummaryCard
                            label="Total Hadir"
                            value={report.summary.total_hadir}
                            total={report.summary.total_peserta}
                            icon="check_circle"
                            bgGradient="bg-gradient-to-br from-teal-300 to-teal-500"
                        />
                    </div>
                    <div className="card-fade-in">
                        <SummaryCard
                            label="Terlambat"
                            value={report.summary.terlambat}
                            total={report.summary.total_peserta}
                            icon="schedule"
                            bgGradient="bg-gradient-to-br from-rose-300 to-rose-500"
                        />
                    </div>
                    <div className="card-fade-in">
                        <SummaryCard
                            label="Izin / Sakit"
                            value={report.summary.izin}
                            total={report.summary.total_peserta}
                            icon="event_note"
                            bgGradient="bg-gradient-to-br from-blue-300 to-blue-500"
                        />
                    </div>
                    <div className="card-fade-in">
                        <SummaryCard
                            label="Tidak Hadir"
                            value={report.summary.tidak_hadir}
                            total={report.summary.total_peserta}
                            icon="person_off"
                            bgGradient="bg-gradient-to-br from-sky-300 to-sky-500"
                        />
                    </div>
                </div>

                {/* 3. Attendance Distribution Chart */}
                <Suspense
                    fallback={
                        <div className="flex h-[460px] items-center justify-center rounded-xl border border-outline-variant bg-white shadow-sm">
                            <span className="text-sm text-on-surface-variant">
                                Memuat grafik…
                            </span>
                        </div>
                    }
                >
                    <AttendanceChart chart={report.chart} />
                </Suspense>

                {/* 4. Reporting Table */}
                <div className="overflow-hidden rounded-xl border border-outline-variant bg-white shadow-sm">
                    <TableToolbar
                        search={tableSearch}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Cari nama, NIM, divisi..."
                        perPage={tablePerPage}
                        onPerPageChange={handlePerPageChange}
                    />
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-outline-variant text-sm">
                            <thead className="bg-[#eab308]">
                                <tr className="divide-x divide-yellow-400">
                                    {[
                                        "NIM",
                                        "Nama",
                                        "Tanggal",
                                        "Program Magang",
                                        "Divisi",
                                        "Hari",
                                        "Hari Kerja",
                                        "Jenis Absen",
                                        "Check In Skedul",
                                        "Check In",
                                        "Check In Lat",
                                        "Check In Long",
                                        "Check Out Skedul",
                                        "Check Out",
                                        "Check Out Lat",
                                        "Check Out Long",
                                        "Mood Masuk",
                                        "Mood Pulang",
                                        "Aksi",
                                    ].map((heading) => (
                                        <th
                                            key={heading}
                                            className="whitespace-nowrap px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-white"
                                        >
                                            {heading}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-outline-variant">
                                {report.rows.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={19}
                                            className="px-3 py-8 text-center text-on-surface-variant"
                                        >
                                            Tidak ada peserta untuk filter ini.
                                        </td>
                                    </tr>
                                ) : (
                                    pagedRows.map((row) => (
                                        <ReportRow
                                            key={`${row.intern_id}-${row.tanggal}`}
                                            row={row}
                                        />
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                    {/* Table Footer */}
                    <div className="flex flex-col items-center justify-between gap-3 border-t border-outline-variant bg-surface-container-lowest px-6 py-4 sm:flex-row">
                        <p className="text-sm text-on-surface-variant">
                            Menampilkan{" "}
                            <span className="font-bold text-on-surface">
                                {from} - {to}
                            </span>{" "}
                            dari{" "}
                            <span className="font-bold text-on-surface">
                                {totalFiltered}
                            </span>{" "}
                            entitas
                        </p>
                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                onClick={() =>
                                    setTablePage((p) => Math.max(1, p - 1))
                                }
                                disabled={safePage <= 1}
                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition hover:bg-surface-container-low disabled:opacity-40"
                            >
                                <MaterialIcon
                                    name="chevron_left"
                                    style={{ fontSize: 18 }}
                                />
                            </button>
                            {Array.from({ length: totalPages }, (_, i) => i + 1)
                                .filter(
                                    (p) =>
                                        p === 1 ||
                                        p === totalPages ||
                                        Math.abs(p - safePage) <= 1,
                                )
                                .reduce<(number | "...")[]>(
                                    (acc, p, idx, arr) => {
                                        if (
                                            idx > 0 &&
                                            (p as number) -
                                                (arr[idx - 1] as number) >
                                                1
                                        )
                                            acc.push("...");
                                        acc.push(p);
                                        return acc;
                                    },
                                    [],
                                )
                                .map((p, idx) =>
                                    p === "..." ? (
                                        <span
                                            key={`ellipsis-${idx}`}
                                            className="px-1 text-sm text-on-surface-variant"
                                        >
                                            …
                                        </span>
                                    ) : (
                                        <button
                                            key={p}
                                            type="button"
                                            onClick={() =>
                                                setTablePage(p as number)
                                            }
                                            className={`inline-flex h-8 w-8 items-center justify-center rounded-lg border text-sm transition ${
                                                p === safePage
                                                    ? "border-primary bg-primary font-bold text-white"
                                                    : "border-outline-variant text-on-surface-variant hover:bg-surface-container-low"
                                            }`}
                                        >
                                            {p}
                                        </button>
                                    ),
                                )}
                            <button
                                type="button"
                                onClick={() =>
                                    setTablePage((p) =>
                                        Math.min(totalPages, p + 1),
                                    )
                                }
                                disabled={safePage >= totalPages}
                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant transition hover:bg-surface-container-low disabled:opacity-40"
                            >
                                <MaterialIcon
                                    name="chevron_right"
                                    style={{ fontSize: 18 }}
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function ReportRow({ row }: { row: AttendanceReportRow }) {
    return (
        <tr className="divide-x divide-outline-variant hover:bg-gray-50">
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.nim)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 font-medium text-gray-900">
                {dash(row.nama)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {formatDate(row.tanggal)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.program)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.divisi)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {row.hari}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {row.hari_kerja ? "Ya" : "Tidak"}
            </td>
            <td className="whitespace-nowrap px-3 py-3">
                <span
                    className={
                        "inline-flex rounded-full px-2 py-0.5 text-xs font-medium " +
                        categoryBadge[row.category]
                    }
                >
                    {row.jenis_absen}
                </span>
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.check_in_schedule)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.check_in)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_in_lat)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_in_long)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.check_out_schedule)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-900">
                {dash(row.check_out)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_out_lat)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_out_long)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-600">
                {dash(row.mood_in)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-600">
                {dash(row.mood_out)}
            </td>
            <td className="whitespace-nowrap px-3 py-3">
                {row.attendance_id ? (
                    <Link
                        href={route(
                            "admin.attendances.edit",
                            row.attendance_id,
                        )}
                        className="text-sm font-medium text-tertiary hover:underline"
                    >
                        Ubah
                    </Link>
                ) : (
                    <span className="text-gray-300">-</span>
                )}
            </td>
        </tr>
    );
}
