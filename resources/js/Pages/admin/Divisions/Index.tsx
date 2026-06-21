import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import DivisionFormDialog from "./Partials/DivisionFormDialog";

type DivisionRow = Division & { interns_count: number };

export default function Index({
    divisions,
    perPage,
}: {
    divisions: Paginated<DivisionRow>;
    perPage: number;
}) {
    const [search, setSearch] = useState("");
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<DivisionRow | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<DivisionRow | null>(null);

    const rows = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return divisions.data;
        }

        return divisions.data.filter((division) =>
            [
                division.name,
                division.is_active ? "Aktif" : "Nonaktif",
                String(division.interns_count),
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [divisions.data, search]);

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

    const changePerPage = (value: number) => {
        router.get(
            route("admin.divisions.index"),
            { perPage: value },
            { preserveScroll: true, preserveState: true, replace: true },
        );
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
                    search={search}
                    onSearchChange={setSearch}
                    perPage={perPage}
                    onPerPageChange={changePerPage}
                />

                <DataTable
                    columns={columns}
                    rows={rows}
                    getRowKey={(division) => division.id}
                    emptyIcon="apartment"
                    emptyText={
                        search
                            ? "Tidak ada divisi yang cocok."
                            : "Belum ada divisi."
                    }
                />

                <TableFooter
                    from={divisions.from}
                    to={divisions.to}
                    total={divisions.total}
                    links={divisions.links}
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
