import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
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
import { Paginated, StudyProgram, University } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useState } from "react";
import StudyProgramFormDialog from "./Partials/StudyProgramFormDialog";

type StudyProgramRow = StudyProgram & {
    interns_count: number;
    university?: University | null;
};

interface Filters {
    search: string;
    status: string;
    university_id: number | null;
}

export default function Index({
    studyPrograms,
    selectedUniversity,
    filters,
}: {
    studyPrograms: Paginated<StudyProgramRow>;
    selectedUniversity: University | null;
    filters: Filters;
}) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [universityId, setUniversityId] = useState<number | string>(
        filters.university_id ?? "",
    );

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<StudyProgramRow | null>(null);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<StudyProgramRow | null>(null);

    const applyFilters = (next: {
        search?: string;
        status?: string;
        university_id?: number | string;
    }) => {
        router.get(
            route("admin.study-programs.index"),
            {
                search: next.search ?? search,
                status: next.status ?? status,
                university_id: next.university_id ?? universityId,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleUniversityFilter = (option: AutocompleteOption | null) => {
        const next = option ? option.id : "";
        setUniversityId(next);
        applyFilters({ university_id: next });
    };

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (program: StudyProgramRow) => {
        setEditing(program);
        setFormOpen(true);
    };

    const openDelete = (program: StudyProgramRow) => {
        setDeleting(program);
        setDeleteOpen(true);
    };

    const columns: Column<StudyProgramRow>[] = [
        {
            header: "Nama",
            cell: (program) => (
                <span className="font-semibold text-on-surface">
                    {program.name}
                </span>
            ),
        },
        {
            header: "Jenjang",
            cell: (program) => program.level ?? "-",
        },
        {
            header: "Perguruan Tinggi",
            cell: (program) => program.university?.name ?? "-",
        },
        {
            header: "Peserta",
            align: "center",
            cell: (program) => program.interns_count,
        },
        {
            header: "Status",
            align: "center",
            cell: (program) =>
                program.is_active ? (
                    <StatusBadge tone="success">Aktif</StatusBadge>
                ) : (
                    <StatusBadge tone="neutral">Nonaktif</StatusBadge>
                ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (program) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah program studi"
                        tone="edit"
                        onClick={() => openEdit(program)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus program studi"
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
                <PageHeader title="Program Studi">
                    <ActionButton label="Tambah" onClick={openCreate} />
                </PageHeader>
            }
        >
            <Head title="Program Studi" />

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
                <FilterField label="Perguruan Tinggi">
                    <Autocomplete
                        url={route("lookup.universities")}
                        value={universityId}
                        displayValue={selectedUniversity?.name ?? ""}
                        placeholder="Semua / cari perguruan tinggi..."
                        onSelect={handleUniversityFilter}
                    />
                </FilterField>

                <FilterField label="Cari nama program studi" htmlFor="search">
                    <input
                        id="search"
                        type="text"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Ketik lalu tekan Enter..."
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
                    rows={studyPrograms.data}
                    getRowKey={(program) => program.id}
                    emptyIcon="menu_book"
                    emptyText="Tidak ada program studi yang cocok."
                />

                <TableFooter
                    from={studyPrograms.from}
                    to={studyPrograms.to}
                    total={studyPrograms.total}
                    links={studyPrograms.links}
                />
            </TableCard>

            <StudyProgramFormDialog
                studyProgram={editing ?? undefined}
                university={selectedUniversity}
                open={formOpen}
                onOpenChange={setFormOpen}
            />

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus program studi ini?"
                description={
                    <>
                        Program studi{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.name}
                        </span>{" "}
                        akan dihapus permanen.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route("admin.study-programs.destroy", deleting.id)
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
