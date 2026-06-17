import Badge from "@/Components/Badge";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { dayOfWeekLabels, formatTime } from "@/lib/labels";
import { WorkingHour } from "@/types";
import { Head, Link } from "@inertiajs/react";

export default function Index({
    workingHours,
}: {
    workingHours: WorkingHour[];
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Jam Kerja
                </h2>
            }
        >
            <Head title="Jam Kerja" />

            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Hari
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jam Masuk
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Jam Pulang
                                </th>
                                <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {workingHours.map((hour) => (
                                <tr key={hour.id}>
                                    <td className="px-4 py-3 font-medium text-gray-900">
                                        {dayOfWeekLabels[hour.day_of_week] ??
                                            hour.day_of_week}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            className={
                                                hour.is_working_day
                                                    ? "bg-green-100 text-green-800"
                                                    : "bg-gray-100 text-gray-800"
                                            }
                                        >
                                            {hour.is_working_day
                                                ? "Hari Kerja"
                                                : "Libur"}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-3 text-sm text-gray-700">
                                        {hour.is_working_day
                                            ? formatTime(hour.start_time)
                                            : "-"}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-gray-700">
                                        {hour.is_working_day
                                            ? formatTime(hour.end_time)
                                            : "-"}
                                    </td>
                                    <td className="px-4 py-3 text-right text-sm">
                                        <Link
                                            href={route(
                                                "admin.working-hours.edit",
                                                hour.id,
                                            )}
                                            className="font-medium text-green-700 hover:underline"
                                        >
                                            Ubah
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
