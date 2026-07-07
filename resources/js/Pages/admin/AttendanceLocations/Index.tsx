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
import { AttendanceLocation } from "@/types";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import LocationFormDialog from "./Partials/LocationFormDialog";

export default function Index({
    locations,
}: {
    locations: AttendanceLocation[];
}) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<AttendanceLocation | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<AttendanceLocation | null>(null);

    const table = useClientTable(locations, (location) =>
        [
            location.name,
            location.latitude,
            location.longitude,
            location.radius,
            location.is_active ? "Aktif" : "Nonaktif",
        ].join(" "),
    );

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

    const hasActiveLocation = locations.some((location) => location.is_active);

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

            {!hasActiveLocation && (
                <div className="mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                    <span className="material-symbols-rounded mt-0.5 text-xl">
                        warning
                    </span>
                    <div className="text-sm">
                        <p className="font-semibold">
                            Belum ada lokasi absensi aktif.
                        </p>
                        <p className="mt-0.5">
                            Check In WFO akan diblokir hingga minimal satu lokasi
                            diaktifkan. Tambahkan atau aktifkan lokasi kantor
                            agar peserta magang dapat melakukan Check In WFO.
                        </p>
                    </div>
                </div>
            )}

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
                    getRowKey={(location) => location.id}
                    emptyIcon="location_off"
                    emptyText={
                        table.search
                            ? "Tidak ada lokasi yang cocok."
                            : "Belum ada lokasi absensi."
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
