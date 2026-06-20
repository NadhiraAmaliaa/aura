import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import { dayOfWeekLabels, formatTime } from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { WorkingHour } from "@/types";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import WorkingHourFormDialog from "./Partials/WorkingHourFormDialog";

export default function Index({
    workingHours,
}: {
    workingHours: WorkingHour[];
}) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<WorkingHour | null>(null);

    const openEdit = (workingHour: WorkingHour) => {
        setEditing(workingHour);
        setFormOpen(true);
    };

    const columns: Column<WorkingHour>[] = [
        {
            header: "Hari",
            cell: (day) => (
                <span className="font-semibold text-on-surface">
                    {dayOfWeekLabels[day.day_of_week] ??
                        String(day.day_of_week)}
                </span>
            ),
        },
        {
            header: "Status",
            cell: (day) =>
                day.is_working_day ? (
                    <StatusBadge tone="success">Hari Kerja</StatusBadge>
                ) : (
                    <StatusBadge tone="neutral">Libur</StatusBadge>
                ),
        },
        {
            header: "Jam Masuk",
            cell: (day) =>
                day.is_working_day && day.start_time
                    ? formatTime(day.start_time)
                    : "-",
        },
        {
            header: "Jam Pulang",
            cell: (day) =>
                day.is_working_day && day.end_time
                    ? formatTime(day.end_time)
                    : "-",
        },
        {
            header: "Aksi",
            align: "center",
            cell: (day) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah jam kerja"
                        tone="edit"
                        onClick={() => openEdit(day)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={<PageHeader title="Jam Kerja" />}
        >
            <Head title="Jam Kerja" />

            <TableCard>
                <DataTable
                    columns={columns}
                    rows={workingHours}
                    getRowKey={(day) => day.id}
                    emptyIcon="schedule"
                    emptyText="Belum ada konfigurasi jam kerja."
                />
            </TableCard>

            <WorkingHourFormDialog
                workingHour={editing}
                open={formOpen}
                onOpenChange={setFormOpen}
            />
        </AuthenticatedLayout>
    );
}
