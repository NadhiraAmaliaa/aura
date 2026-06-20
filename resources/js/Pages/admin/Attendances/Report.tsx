import DonutChart, { DonutSlice } from "@/Components/DonutChart";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { formatDate } from "@/lib/labels";
import {
    AttendanceReport,
    AttendanceReportCategory,
    AttendanceReportFilters,
    AttendanceReportRow,
    Division,
    InternProgram,
} from "@/types";
import { Head, Link, router, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

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
    accent,
}: {
    label: string;
    value: number;
    accent: string;
}) {
    return (
        <div className="rounded-xl border border-outline-variant bg-white p-5 shadow-sm">
            <p className="text-sm font-medium text-on-surface-variant">
                {label}
            </p>
            <p className={"mt-2 text-3xl font-bold " + accent}>{value}</p>
        </div>
    );
}

export default function Report({
    report,
    programs,
    divisions,
    filters,
}: ReportPageProps) {
    const { data, setData, get, processing } = useForm({
        start_date: filters.start_date,
        end_date: filters.end_date,
        program: filters.program ? String(filters.program) : "",
        division: filters.division ? String(filters.division) : "",
    });

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

    const exportUrl = route("admin.attendances.export", {
        start_date: data.start_date,
        end_date: data.end_date,
        program: data.program || undefined,
        division: data.division || undefined,
    });

    const isSameDay = report.start_date === report.end_date;
    const periodLabel = isSameDay
        ? formatDate(report.start_date)
        : `${formatDate(report.start_date)} — ${formatDate(report.end_date)}`;

    const chartSlices: DonutSlice[] = [
        { label: "WFO", value: report.chart.wfo, color: "#16a34a" },
        { label: "WFH", value: report.chart.wfh, color: "#0ea5e9" },
        { label: "Dinas", value: report.chart.dinas, color: "#9333ea" },
        { label: "Izin", value: report.chart.izin, color: "#6366f1" },
        {
            label: "Tidak Hadir",
            value: report.chart.tidak_hadir,
            color: "#ef4444",
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Reporting Absensi
                </h1>
            }
        >
            <Head title="Reporting Absensi" />

            <div className="space-y-6">
                {/* 1. Filter Section */}
                <form
                    onSubmit={submit}
                    className="rounded-xl border border-outline-variant bg-white p-5 shadow-sm"
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                        <div>
                            <InputLabel
                                htmlFor="start_date"
                                value="Tanggal Awal"
                            />
                            <TextInput
                                id="start_date"
                                type="date"
                                className="mt-1 block w-full"
                                value={data.start_date}
                                onChange={(e) =>
                                    setData("start_date", e.target.value)
                                }
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="end_date"
                                value="Tanggal Akhir"
                            />
                            <TextInput
                                id="end_date"
                                type="date"
                                className="mt-1 block w-full"
                                value={data.end_date}
                                min={data.start_date}
                                onChange={(e) =>
                                    setData("end_date", e.target.value)
                                }
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="program"
                                value="Program Magang"
                            />
                            <SelectInput
                                id="program"
                                className="mt-1 block w-full"
                                value={data.program}
                                onChange={(e) =>
                                    setData("program", e.target.value)
                                }
                            >
                                <option value="">Semua Program</option>
                                {programs.map((program) => (
                                    <option key={program.id} value={program.id}>
                                        {program.name}
                                    </option>
                                ))}
                            </SelectInput>
                        </div>

                        <div>
                            <InputLabel htmlFor="division" value="Divisi" />
                            <SelectInput
                                id="division"
                                className="mt-1 block w-full"
                                value={data.division}
                                onChange={(e) =>
                                    setData("division", e.target.value)
                                }
                            >
                                <option value="">Semua Divisi</option>
                                {divisions.map((division) => (
                                    <option
                                        key={division.id}
                                        value={division.id}
                                    >
                                        {division.name}
                                    </option>
                                ))}
                            </SelectInput>
                        </div>

                        <div className="flex items-end gap-2">
                            <PrimaryButton type="submit" disabled={processing}>
                                Filter
                            </PrimaryButton>
                            <SecondaryButton
                                type="button"
                                onClick={reset}
                                disabled={processing}
                            >
                                Reset
                            </SecondaryButton>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-outline-variant pt-4">
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
                </form>

                {/* 2. Summary Cards */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <SummaryCard
                        label="Total Peserta"
                        value={report.summary.total_peserta}
                        accent="text-gray-900"
                    />
                    <SummaryCard
                        label="Total Hadir"
                        value={report.summary.total_hadir}
                        accent="text-green-600"
                    />
                    <SummaryCard
                        label="Terlambat"
                        value={report.summary.terlambat}
                        accent="text-yellow-600"
                    />
                    <SummaryCard
                        label="Izin"
                        value={report.summary.izin}
                        accent="text-indigo-600"
                    />
                    <SummaryCard
                        label="Tidak Hadir"
                        value={report.summary.tidak_hadir}
                        accent="text-red-600"
                    />
                </div>

                {/* 3. Attendance Distribution Chart */}
                <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-sm font-semibold uppercase tracking-wide text-on-surface-variant">
                        Distribusi Kehadiran
                    </h3>
                    <DonutChart
                        data={chartSlices}
                        emptyMessage="Belum ada data kehadiran untuk periode ini."
                    />
                </div>

                {/* 4. Reporting Table */}
                <div className="overflow-hidden rounded-xl border border-outline-variant bg-white shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-outline-variant text-sm">
                            <thead className="bg-[#eab308]">
                                <tr>
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
                                            className="whitespace-nowrap px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface"
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
                                    report.rows.map((row) => (
                                        <ReportRow
                                            key={`${row.intern_id}-${row.tanggal}`}
                                            row={row}
                                        />
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function ReportRow({ row }: { row: AttendanceReportRow }) {
    return (
        <tr className="hover:bg-gray-50">
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.nim)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 font-medium text-gray-900">
                {dash(row.nama)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {formatDate(row.tanggal)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.program)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.divisi)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {row.hari}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
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
                {row.is_late && (
                    <span className="ml-1 inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                        Terlambat
                    </span>
                )}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_in_schedule)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_in)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-500">
                {dash(row.check_in_lat)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-500">
                {dash(row.check_in_long)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_out_schedule)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-700">
                {dash(row.check_out)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-500">
                {dash(row.check_out_lat)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-500">
                {dash(row.check_out_long)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-400">
                {dash(row.mood_in)}
            </td>
            <td className="whitespace-nowrap px-3 py-3 text-gray-400">
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
