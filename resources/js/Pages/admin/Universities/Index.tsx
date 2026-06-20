import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import FilterCard, {
    FilterField,
    filterControlClass,
} from "@/Components/admin/FilterCard";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Paginated, University } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";
import UniversityFormDialog from "./Partials/UniversityFormDialog";

type UniversityRow = University & {
    interns_count: number;
    study_programs_count: number;
};

interface Filters {
    search: string;
    status: string;
}

export default function Index({
    universities,
    filters,
}: {
    universities: Paginated<UniversityRow>;
    filters: Filters;
}) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<UniversityRow | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<UniversityRow | null>(null);

    const applyFilters = (next: Partial<Filters>) => {
        router.get(
            route("admin.universities.index"),
            {
                search: next.search ?? search,
                status: next.status ?? status,
            },
            { preserveState: true, replace: true },
        );
    };

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (university: UniversityRow) => {
        setEditing(university);
        setFormOpen(true);
    };

    const openDelete = (university: UniversityRow) => {
        setDeleting(university);
        setDeleteOpen(true);
    };

    const isUsed = (university: UniversityRow) =>
        university.interns_count > 0 || university.study_programs_count > 0;

    const columns: Column<UniversityRow>[] = [
        {
            header: "Nama",
            cell: (university) => (
                <span className="font-semibold text-on-surface">
                    {university.name}
                </span>
            ),
        },
        {
            header: "LLDikti",
            cell: (university) => university.lldikti ?? "-",
        },
        {
            header: "Prodi",
            align: "center",
            cell: (university) => (
                <Link
                    href={route("admin.study-programs.index", {
                        university_id: university.id,
                    })}
                    className="font-semibold text-tertiary hover:underline"
                >
                    {university.study_programs_count}
                </Link>
            ),
        },
        {
            header: "Peserta",
            align: "center",
            cell: (university) => university.interns_count,
        },
        {
            header: "Status",
            align: "center",
            cell: (university) =>
                university.is_active ? (
                    <StatusBadge tone="success">Aktif</StatusBadge>
                ) : (
                    <StatusBadge tone="neutral">Nonaktif</StatusBadge>
                ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (university) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah perguruan tinggi"
                        tone="edit"
                        onClick={() => openEdit(university)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus perguruan tinggi"
                        tone="delete"
                        disabled={isUsed(university)}
                        onClick={() => openDelete(university)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Perguruan Tinggi">
                    <ActionButton label="Tambah" onClick={openCreate} />
                </PageHeader>
            }
        >
            <Head title="Perguruan Tinggi" />

            <FilterCard
                onSubmit={(event) => {
                    event.preventDefault();
                    applyFilters({});
                }}
                actions={
                    <button
                        type="submit"
                        className="inline-flex h-10 items-center rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary/90"
                    >
                        Cari
                    </button>
                }
            >
                <FilterField label="Cari nama perguruan tinggi" htmlFor="search">
                    <input
                        id="search"
                        type="text"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Ketik nama lalu tekan Enter..."
                        className={filterControlClass}
                    />
                </FilterField>

                <FilterField label="Status" htmlFor="status">
                    <select
                        id="status"
                        value={status}
                        onChange={(event) => {
                            setStatus(event.target.value);
                            applyFilters({ status: event.target.value });
                        }}
                        className={filterControlClass}
                    >
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </FilterField>
            </FilterCard>

            <TableCard>
                <DataTable
                    columns={columns}
                    rows={universities.data}
                    getRowKey={(university) => university.id}
                    emptyIcon="school"
                    emptyText="Tidak ada perguruan tinggi yang cocok."
                />

                <TableFooter
                    from={universities.from}
                    to={universities.to}
                    total={universities.total}
                    links={universities.links}
                />
            </TableCard>

            <UniversityFormDialog
                university={editing ?? undefined}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus perguruan tinggi ini?"
                description={
                    <>
                        Perguruan tinggi{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.name}
                        </span>{" "}
                        akan dihapus permanen.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route("admin.universities.destroy", deleting.id)
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
