import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { InternProgram, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import InternProgramFormDialog from "./Partials/InternProgramFormDialog";

type ProgramRow = InternProgram & { interns_count: number };

export default function Index({
    programs,
    perPage,
}: {
    programs: Paginated<ProgramRow>;
    perPage: number;
}) {
    const [search, setSearch] = useState("");
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<ProgramRow | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<ProgramRow | null>(null);

    const rows = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return programs.data;
        }

        return programs.data.filter((program) =>
            [
                program.name,
                program.description ?? "",
                String(program.interns_count),
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [programs.data, search]);

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

    const changePerPage = (value: number) => {
        router.get(
            route("admin.intern-programs.index"),
            { perPage: value },
            { preserveScroll: true, preserveState: true, replace: true },
        );
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
                    search={search}
                    onSearchChange={setSearch}
                    perPage={perPage}
                    onPerPageChange={changePerPage}
                />

                <DataTable
                    columns={columns}
                    rows={rows}
                    getRowKey={(program) => program.id}
                    emptyIcon="school"
                    emptyText={
                        search
                            ? "Tidak ada program yang cocok."
                            : "Belum ada program magang."
                    }
                />

                <TableFooter
                    from={programs.from}
                    to={programs.to}
                    total={programs.total}
                    links={programs.links}
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
