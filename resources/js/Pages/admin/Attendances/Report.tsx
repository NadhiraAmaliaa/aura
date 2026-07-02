import DatePicker from "@/Components/admin/DatePicker";
import FilterCard, { FilterField } from "@/Components/admin/FilterCard";
import FilterSelect, {
    FilterSelectOption,
} from "@/Components/admin/FilterSelect";
import MaterialIcon from "@/Components/MaterialIcon";
import SummaryCard from "@/Components/admin/SummaryCard";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { formatDate } from "@/lib/labels";
import {
    AttendanceReport,
    AttendanceReportFilters,
    Division,
    InternProgram,
    PageProps,
} from "@/types";
import { Head, router, useForm, usePage } from "@inertiajs/react";
import { FormEventHandler, lazy, Suspense, useMemo, useState } from "react";

import { animationStyles } from "./reportAnimations";
import ReportRow from "./ReportRow";

const AttendanceChart = lazy(() => import("@/Components/AttendanceChart"));

interface ReportPageProps {
    report: AttendanceReport;
    programs: InternProgram[];
    divisions: Division[];
    filters: AttendanceReportFilters;
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
