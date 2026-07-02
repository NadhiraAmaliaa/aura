import { formatDate } from "@/lib/labels";
import { AttendanceReportCategory, AttendanceReportRow } from "@/types";
import { Link } from "@inertiajs/react";

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

export default function ReportRow({ row }: { row: AttendanceReportRow }) {
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
