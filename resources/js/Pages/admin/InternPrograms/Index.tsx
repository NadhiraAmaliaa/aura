import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import TableCard from "@/Components/admin/TableCard";
import ClientTableFooter from "@/Components/admin/ClientTableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useClientTable } from "@/lib/useClientTable";
import { InternProgram } from "@/types";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import InternProgramFormDialog from "./Partials/InternProgramFormDialog";

type ProgramRow = InternProgram & { interns_count: number };

export default function Index({ programs }: { programs: ProgramRow[] }) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<ProgramRow | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<ProgramRow | null>(null);

    const table = useClientTable(programs, (program) =>
        [
            program.name,
            program.description ?? "",
            String(program.interns_count),
        ].join(" "),
    );

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (program: ProgramRow) => {
        setEditing(program);
        setFormOpen(true);
    };

    const openDelete = (program: ProgramRow) => {
        setDeleting(program);
        setDeleteOpen(true);
    };

    const columns: Column<ProgramRow>[] = [
        {
            header: "Nama",
            cell: (program) => (
                <span className="font-semibold text-on-surface">
                    {program.name}
                </span>
            ),
        },
        {
            header: "Deskripsi",
            cell: (program) => (
                <span className="text-on-surface-variant">
                    {program.description ?? "-"}
                </span>
            ),
        },
        {
            header: "Peserta",
            align: "center",
            cell: (program) => (
                <span className="font-medium">{program.interns_count}</span>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (program) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah program"
                        tone="edit"
                        onClick={() => openEdit(program)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus program"
                        tone="delete"
                        disabled={program.interns_count > 0}
                        onClick={() => openDelete(program)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Program Magang">
                    <ActionButton label="Tambah" onClick={openCreate} />
                </PageHeader>
            }
        >
            <Head title="Program Magang" />

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
                    getRowKey={(program) => program.id}
                    emptyIcon="school"
                    emptyText={
                        table.search
                            ? "Tidak ada program yang cocok."
                            : "Belum ada program magang."
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

            <InternProgramFormDialog
                program={editing ?? undefined}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus program magang ini?"
                description={
                    <>
                        Program{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.name}
                        </span>{" "}
                        akan dihapus permanen.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route("admin.intern-programs.destroy", deleting.id)
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
