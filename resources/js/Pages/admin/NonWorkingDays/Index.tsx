import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import { formatDate, nonWorkingDayTypeLabels } from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { NonWorkingDay, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import NonWorkingDayFormDialog from "./Partials/NonWorkingDayFormDialog";

export default function Index({
    nonWorkingDays,
    perPage,
}: {
    nonWorkingDays: Paginated<NonWorkingDay>;
    perPage: number;
}) {
    const [search, setSearch] = useState("");
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<NonWorkingDay | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<NonWorkingDay | null>(null);

    const rows = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return nonWorkingDays.data;
        }

        return nonWorkingDays.data.filter((day) =>
            [
                day.name,
                day.date,
                formatDate(day.date),
                nonWorkingDayTypeLabels[day.type],
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [nonWorkingDays.data, search]);

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

    const changePerPage = (value: number) => {
        router.get(
            route("admin.non-working-days.index"),
            { perPage: value },
            { preserveScroll: true, preserveState: true, replace: true },
        );
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
                    search={search}
                    onSearchChange={setSearch}
                    perPage={perPage}
                    onPerPageChange={changePerPage}
                />

                <DataTable
                    columns={columns}
                    rows={rows}
                    getRowKey={(day) => day.id}
                    emptyIcon="event_busy"
                    emptyText={
                        search
                            ? "Tidak ada hari libur yang cocok."
                            : "Belum ada hari libur."
                    }
                />

                <TableFooter
                    from={nonWorkingDays.from}
                    to={nonWorkingDays.to}
                    total={nonWorkingDays.total}
                    links={nonWorkingDays.links}
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
