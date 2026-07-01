import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import ClientTableFooter from "@/Components/admin/ClientTableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import { formatDate, nonWorkingDayTypeLabels } from "@/lib/labels";
import { useClientTable } from "@/lib/useClientTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { NonWorkingDay } from "@/types";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import NonWorkingDayFormDialog from "./Partials/NonWorkingDayFormDialog";

export default function Index({
    nonWorkingDays,
}: {
    nonWorkingDays: NonWorkingDay[];
}) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<NonWorkingDay | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<NonWorkingDay | null>(null);

    const table = useClientTable(nonWorkingDays, (day) =>
        [
            day.name,
            day.date,
            formatDate(day.date),
            nonWorkingDayTypeLabels[day.type],
        ].join(" "),
    );

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (day: NonWorkingDay) => {
        setEditing(day);
        setFormOpen(true);
    };

    const openDelete = (day: NonWorkingDay) => {
        setDeleting(day);
        setDeleteOpen(true);
    };

    const columns: Column<NonWorkingDay>[] = [
        {
            header: "Tanggal",
            cell: (day) => (
                <span className="font-semibold text-on-surface">
                    {formatDate(day.date)}
                </span>
            ),
        },
        {
            header: "Nama",
            cell: (day) => day.name,
        },
        {
            header: "Jenis",
            cell: (day) => (
                <StatusBadge tone="info">
                    {nonWorkingDayTypeLabels[day.type]}
                </StatusBadge>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (day) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah hari libur"
                        tone="edit"
                        onClick={() => openEdit(day)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus hari libur"
                        tone="delete"
                        onClick={() => openDelete(day)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Hari Libur">
                    <ActionButton label="Tambah" onClick={openCreate} />
                </PageHeader>
            }
        >
            <Head title="Hari Libur" />

            <TableCard>
                <TableToolbar
                    search={table.search}
                    onSearchChange={table.onSearchChange}
                    perPage={table.perPage}
                    onPerPageChange={table.onPerPageChange}
                />

                <DataTable
                    columns={columns}
                    rows={table.rows}
                    getRowKey={(day) => day.id}
                    emptyIcon="event_busy"
                    emptyText={
                        table.search
                            ? "Tidak ada hari libur yang cocok."
                            : "Belum ada hari libur."
                    }
                />

                <ClientTableFooter
                    from={table.from}
                    to={table.to}
                    total={table.total}
                    page={table.page}
                    totalPages={table.totalPages}
                    onPageChange={table.setPage}
                />
            </TableCard>

            <NonWorkingDayFormDialog
                nonWorkingDay={editing ?? undefined}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus hari libur ini?"
                description={
                    <>
                        Hari libur{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.name}
                        </span>{" "}
                        akan dihapus permanen.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route("admin.non-working-days.destroy", deleting.id)
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
