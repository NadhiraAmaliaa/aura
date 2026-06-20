import MaterialIcon from "@/Components/MaterialIcon";
import Pagination from "@/Components/Pagination";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { AttendanceLocation, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import DeleteLocationDialog from "./Partials/DeleteLocationDialog";
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
            [location.name, location.latitude, location.longitude]
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

    return (
        <AuthenticatedLayout
            header={
                <>
                    <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                        Lokasi Absen
                    </h1>
                    <button
                        type="button"
                        onClick={openCreate}
                        className="flex h-11 items-center justify-center gap-2 rounded-lg bg-[#28a745] px-6 font-bold text-white shadow-sm transition-all hover:brightness-95 active:scale-95"
                    >
                        <MaterialIcon
                            name="add_circle"
                            style={{ fontSize: "18px" }}
                        />
                        Tambah
                    </button>
                </>
            }
        >
            <Head title="Lokasi Absen" />

            <section className="overflow-hidden rounded-xl border border-outline-variant bg-white shadow-sm">
                {/* Toolbar */}
                <div className="flex flex-col items-center justify-between gap-4 border-b border-outline-variant bg-surface-container-lowest px-6 py-4 md:flex-row">
                    <div className="flex items-center gap-2 text-sm text-on-surface-variant">
                        <span>Tampilkan</span>
                        <select
                            value={perPage}
                            onChange={(event) =>
                                changePerPage(Number(event.target.value))
                            }
                            className="h-9 rounded-lg border-outline-variant bg-surface-container-low px-2 text-sm font-medium focus:border-primary focus:ring-primary"
                        >
                            <option value={10}>10</option>
                            <option value={25}>25</option>
                            <option value={50}>50</option>
                            <option value={100}>100</option>
                        </select>
                        <span>data</span>
                    </div>

                    <div className="relative w-full md:w-64">
                        <MaterialIcon
                            name="search"
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
                            style={{ fontSize: "20px" }}
                        />
                        <input
                            type="text"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Pencarian:"
                            className="h-9 w-full rounded-lg border-outline-variant bg-surface-container-low pl-10 pr-4 text-sm focus:border-primary focus:ring-primary"
                        />
                    </div>
                </div>

                {/* Table */}
                <div className="overflow-x-auto">
                    <table className="w-full border-collapse">
                        <thead>
                            <tr className="bg-[#eab308] text-left text-xs font-semibold uppercase tracking-wider text-white">
                                <th className="whitespace-nowrap border-r border-white/20 px-6 py-2">
                                    Nama
                                </th>
                                <th className="whitespace-nowrap border-r border-white/20 px-6 py-2">
                                    Latitude
                                </th>
                                <th className="whitespace-nowrap border-r border-white/20 px-6 py-2">
                                    Longitude
                                </th>
                                <th className="whitespace-nowrap border-r border-white/20 px-6 py-2">
                                    Radius
                                </th>
                                <th className="whitespace-nowrap px-6 py-2 text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-outline-variant">
                            {rows.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-6 py-16">
                                        <div className="flex flex-col items-center justify-center gap-2 text-center">
                                            <MaterialIcon
                                                name="location_off"
                                                className="text-on-surface-variant/40"
                                                style={{ fontSize: "32px" }}
                                            />
                                            <p className="text-sm font-medium text-on-surface-variant">
                                                {search
                                                    ? "Tidak ada lokasi yang cocok."
                                                    : "Belum ada lokasi absensi."}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            ) : (
                                rows.map((location, index) => (
                                    <tr
                                        key={location.id}
                                        className={
                                            "group transition-colors hover:bg-surface-container-lowest " +
                                            (index % 2 === 1
                                                ? "bg-surface-container-low/30"
                                                : "")
                                        }
                                    >
                                        <td className="border-r border-outline-variant px-6 py-3 font-semibold text-on-surface">
                                            {location.name}
                                        </td>
                                        <td className="border-r border-outline-variant px-6 py-3 font-mono text-sm text-on-surface">
                                            {location.latitude}
                                        </td>
                                        <td className="border-r border-outline-variant px-6 py-3 font-mono text-sm text-on-surface">
                                            {location.longitude}
                                        </td>
                                        <td className="border-r border-outline-variant px-6 py-3">
                                            <span className="inline-flex items-center rounded-full bg-surface-container px-2.5 py-0.5 text-xs font-medium text-tertiary">
                                                {location.radius}
                                            </span>
                                        </td>
                                        <td className="px-6 py-3">
                                            <div className="flex justify-center gap-2 opacity-60 transition-opacity group-hover:opacity-100">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        openEdit(location)
                                                    }
                                                    aria-label="Ubah lokasi"
                                                    className="rounded-lg bg-tertiary/10 p-1.5 text-tertiary transition-all hover:bg-tertiary hover:text-white"
                                                >
                                                    <MaterialIcon
                                                        name="edit"
                                                        style={{
                                                            fontSize: "18px",
                                                        }}
                                                    />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        openDelete(location)
                                                    }
                                                    aria-label="Hapus lokasi"
                                                    className="rounded-lg bg-error/10 p-1.5 text-error transition-all hover:bg-error hover:text-white"
                                                >
                                                    <MaterialIcon
                                                        name="delete"
                                                        style={{
                                                            fontSize: "18px",
                                                        }}
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Footer */}
                <div className="flex items-center justify-between border-t border-outline-variant bg-surface-container-lowest px-6 py-4">
                    <p className="text-sm text-on-surface-variant">
                        Menampilkan{" "}
                        <span className="font-bold text-on-surface">
                            {locations.from ?? 0} - {locations.to ?? 0}
                        </span>{" "}
                        dari{" "}
                        <span className="font-bold text-on-surface">
                            {locations.total}
                        </span>{" "}
                        entitas
                    </p>
                    <Pagination links={locations.links} />
                </div>
            </section>

            <LocationFormDialog
                location={editing ?? undefined}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <DeleteLocationDialog
                location={deleting}
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
            />
        </AuthenticatedLayout>
    );
}
