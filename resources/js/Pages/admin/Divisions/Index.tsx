import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import ClientTableFooter from "@/Components/admin/ClientTableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useClientTable } from "@/lib/useClientTable";
import { Division } from "@/types";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import DivisionFormDialog from "./Partials/DivisionFormDialog";

type DivisionRow = Division & { interns_count: number };

export default function Index({ divisions }: { divisions: DivisionRow[] }) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<DivisionRow | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<DivisionRow | null>(null);

    const table = useClientTable(divisions, (division) =>
        [
            division.name,
            division.is_active ? "Aktif" : "Nonaktif",
            String(division.interns_count),
        ].join(" "),
    );

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (division: DivisionRow) => {
        setEditing(division);
        setFormOpen(true);
    };

    const openDelete = (division: DivisionRow) => {
        setDeleting(division);
        setDeleteOpen(true);
    };

    const columns: Column<DivisionRow>[] = [
        {
            header: "Nama",
            cell: (division) => (
                <span className="font-semibold text-on-surface">
                    {division.name}
                </span>
            ),
        },
        {
            header: "Status",
            cell: (division) => (
                <StatusBadge tone={division.is_active ? "success" : "neutral"}>
                    {division.is_active ? "Aktif" : "Nonaktif"}
                </StatusBadge>
            ),
        },
        {
            header: "Peserta",
            align: "center",
            cell: (division) => (
                <span className="font-medium">{division.interns_count}</span>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (division) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah divisi"
                        tone="edit"
                        onClick={() => openEdit(division)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus divisi"
                        tone="delete"
                        disabled={division.interns_count > 0}
                        onClick={() => openDelete(division)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Divisi">
                    <ActionButton label="Tambah" onClick={openCreate} />
                </PageHeader>
            }
        >
            <Head title="Divisi" />

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
                    getRowKey={(division) => division.id}
                    emptyIcon="apartment"
                    emptyText={
                        table.search
                            ? "Tidak ada divisi yang cocok."
                            : "Belum ada divisi."
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

            <DivisionFormDialog
                division={editing ?? undefined}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus divisi ini?"
                description={
                    <>
                        Divisi{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.name}
                        </span>{" "}
                        akan dihapus permanen.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route("admin.divisions.destroy", deleting.id)
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
