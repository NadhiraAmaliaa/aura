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
import { AttendanceLocation, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import LocationFormDialog from "./Partials/LocationFormDialog";

export default function Index({
    locations,
    perPage,
}: {
    locations: Paginated<AttendanceLocation>;
    perPage: number;
}) {
    const [search, setSearch] = useState("");
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<AttendanceLocation | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<AttendanceLocation | null>(null);

    const rows = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return locations.data;
        }

        return locations.data.filter((location) =>
            [
                location.name,
                location.latitude,
                location.longitude,
                location.radius,
                location.is_active ? "Aktif" : "Nonaktif",
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [locations.data, search]);

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (location: AttendanceLocation) => {
        setEditing(location);
        setFormOpen(true);
    };

    const openDelete = (location: AttendanceLocation) => {
        setDeleting(location);
        setDeleteOpen(true);
    };

    const changePerPage = (value: number) => {
        router.get(
            route("admin.attendance-locations.index"),
            { perPage: value },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    const columns: Column<AttendanceLocation>[] = [
        {
            header: "Nama",
            cell: (location) => (
                <span className="font-semibold text-on-surface">
                    {location.name}
                </span>
            ),
        },
        {
            header: "Latitude",
            cell: (location) => (
                <span className="font-mono">{location.latitude}</span>
            ),
        },
        {
            header: "Longitude",
            cell: (location) => (
                <span className="font-mono">{location.longitude}</span>
            ),
        },
        {
            header: "Radius",
            cell: (location) => (
                <StatusBadge tone="info">{location.radius}</StatusBadge>
            ),
        },
        {
            header: "Status",
            cell: (location) => (
                <StatusBadge tone={location.is_active ? "success" : "neutral"}>
                    {location.is_active ? "Aktif" : "Nonaktif"}
                </StatusBadge>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (location) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah lokasi"
                        tone="edit"
                        onClick={() => openEdit(location)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus lokasi"
                        tone="delete"
                        onClick={() => openDelete(location)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Lokasi Absen">
                    <ActionButton label="Tambah" onClick={openCreate} />
                </PageHeader>
            }
        >
            <Head title="Lokasi Absen" />

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
                    getRowKey={(location) => location.id}
                    emptyIcon="location_off"
                    emptyText={
                        search
                            ? "Tidak ada lokasi yang cocok."
                            : "Belum ada lokasi absensi."
                    }
                />

                <TableFooter
                    from={locations.from}
                    to={locations.to}
                    total={locations.total}
                    links={locations.links}
                />
            </TableCard>

            <LocationFormDialog
                location={editing ?? undefined}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus lokasi ini?"
                description={
                    <>
                        Lokasi{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.name}
                        </span>{" "}
                        akan dihapus permanen dan tidak dapat dikembalikan.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route(
                              "admin.attendance-locations.destroy",
                              deleting.id,
                          )
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
