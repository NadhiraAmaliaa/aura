import ActionButton from "@/Components/admin/ActionButton";
import ConfirmDeleteDialog from "@/Components/admin/ConfirmDeleteDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import FilterCard, {
    FilterField,
    filterControlClass,
} from "@/Components/admin/FilterCard";
import FilterSelect from "@/Components/admin/FilterSelect";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import {
    formatDate,
    internStatusBadgeTone,
    internStatusLabels,
} from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division, Intern, InternProgram, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

interface Filters {
    search: string;
    program: number | null;
    division: number | null;
    status: string;
    period_from: string | null;
    period_to: string | null;
}

export default function Index({
    interns,
    programs,
    divisions,
    filters,
}: {
    interns: Paginated<Intern>;
    programs: Pick<InternProgram, "id" | "name">[];
    divisions: Pick<Division, "id" | "name">[];
    filters: Filters;
}) {
    const [search, setSearch] = useState(filters.search ?? "");
    const [program, setProgram] = useState(
        filters.program ? String(filters.program) : "",
    );
    const [division, setDivision] = useState(
        filters.division ? String(filters.division) : "",
    );
    const [status, setStatus] = useState(filters.status ?? "");
    const [periodFrom, setPeriodFrom] = useState(filters.period_from ?? "");
    const [periodTo, setPeriodTo] = useState(filters.period_to ?? "");

    const [deleteOpen, setDeleteOpen] = useState(false);
    const [deleting, setDeleting] = useState<Intern | null>(null);

    const programOptions = programs.map((p) => ({
        value: String(p.id),
        label: p.name,
    }));

    const divisionOptions = divisions.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));

    const statusOptions = [
        { value: "upcoming", label: "Akan Datang", dot: "#f59e0b" },
        { value: "active", label: "Aktif", dot: "#22c55e" },
        { value: "inactive", label: "Nonaktif", dot: "#9ca3af" },
        { value: "completed", label: "Selesai", dot: "#38bdf8" },
    ];

    const applyFilters = (next: Partial<Record<string, string>>) => {
        router.get(
            route("admin.interns.index"),
            {
                search: next.search ?? search,
                program: next.program ?? program,
                division: next.division ?? division,
                status: next.status ?? status,
                period_from: next.period_from ?? periodFrom,
                period_to: next.period_to ?? periodTo,
            },
            { preserveState: true, replace: true },
        );
    };

    const submitFilters: FormEventHandler = (event) => {
        event.preventDefault();
        applyFilters({});
    };

    const resetFilters = () => {
        setSearch("");
        setProgram("");
        setDivision("");
        setStatus("");
        setPeriodFrom("");
        setPeriodTo("");
        router.get(
            route("admin.interns.index"),
            {},
            { preserveState: true, replace: true },
        );
    };

    const openDelete = (intern: Intern) => {
        setDeleting(intern);
        setDeleteOpen(true);
    };

    const periodText = (intern: Intern) => {
        if (!intern.start_date && !intern.end_date) {
            return "-";
        }

        return `${formatDate(intern.start_date)} - ${formatDate(intern.end_date)}`;
    };

    const columns: Column<Intern>[] = [
        {
            header: "Nama",
            cell: (intern) => (
                <span className="font-semibold text-on-surface">
                    {intern.user?.name}
                </span>
            ),
        },
        {
            header: "NIM",
            cell: (intern) => intern.nim ?? "-",
        },
        {
            header: "Perguruan Tinggi",
            cell: (intern) =>
                intern.university_ref?.name ?? intern.university ?? "-",
        },
        {
            header: "Program Studi",
            cell: (intern) => intern.study_program?.name ?? intern.major ?? "-",
        },
        {
            header: "Divisi",
            cell: (intern) =>
                intern.division_ref?.name ?? intern.division ?? "-",
        },
        {
            header: "Program Magang",
            cell: (intern) => intern.intern_program?.name ?? "-",
        },
        {
            header: "Periode Magang",
            className: "whitespace-nowrap",
            cell: (intern) => periodText(intern),
        },
        {
            header: "Status",
            align: "center",
            cell: (intern) => (
                <StatusBadge tone={internStatusBadgeTone[intern.status]}>
                    {internStatusLabels[intern.status]}
                </StatusBadge>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (intern) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah peserta"
                        tone="edit"
                        href={route("admin.interns.edit", intern.id)}
                    />
                    <IconAction
                        icon="delete"
                        label="Hapus peserta"
                        tone="delete"
                        onClick={() => openDelete(intern)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Peserta Magang">
                    <ActionButton
                        label="Tambah"
                        href={route("admin.interns.create")}
                    />
                </PageHeader>
            }
        >
            <Head title="Peserta Magang" />

            <FilterCard
                onSubmit={submitFilters}
                actions={
                    <>
                        <button
                            type="submit"
                            className="inline-flex h-10 items-center rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary/90"
                        >
                            Terapkan
                        </button>
                        <button
                            type="button"
                            onClick={resetFilters}
                            className="text-sm font-medium text-on-surface-variant hover:text-on-surface"
                        >
                            Reset
                        </button>
                    </>
                }
            >
                <FilterField label="Cari nama atau NIM" htmlFor="search">
                    <input
                        id="search"
                        type="text"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Ketik lalu tekan Enter..."
                        className={filterControlClass}
                    />
                </FilterField>

                <FilterField label="Program Magang" htmlFor="program">
                    <FilterSelect
                        id="program"
                        value={program}
                        options={programOptions}
                        placeholder="Semua program"
                        onChange={(val) => {
                            setProgram(val);
                            applyFilters({ program: val });
                        }}
                    />
                </FilterField>

                <FilterField label="Divisi" htmlFor="division">
                    <FilterSelect
                        id="division"
                        value={division}
                        options={divisionOptions}
                        placeholder="Semua divisi"
                        onChange={(val) => {
                            setDivision(val);
                            applyFilters({ division: val });
                        }}
                    />
                </FilterField>

                <FilterField label="Status" htmlFor="status">
                    <FilterSelect
                        id="status"
                        value={status}
                        options={statusOptions}
                        placeholder="Semua status"
                        onChange={(val) => {
                            setStatus(val);
                            applyFilters({ status: val });
                        }}
                    />
                </FilterField>

                <FilterField label="Periode mulai dari" htmlFor="period_from">
                    <input
                        id="period_from"
                        type="date"
                        value={periodFrom}
                        onChange={(event) => {
                            setPeriodFrom(event.target.value);
                            applyFilters({ period_from: event.target.value });
                        }}
                        className={filterControlClass}
                    />
                </FilterField>

                <FilterField label="Periode sampai" htmlFor="period_to">
                    <input
                        id="period_to"
                        type="date"
                        value={periodTo}
                        onChange={(event) => {
                            setPeriodTo(event.target.value);
                            applyFilters({ period_to: event.target.value });
                        }}
                        className={filterControlClass}
                    />
                </FilterField>
            </FilterCard>

            <TableCard>
                <DataTable
                    columns={columns}
                    rows={interns.data}
                    getRowKey={(intern) => intern.id}
                    emptyIcon="groups"
                    emptyText="Tidak ada peserta magang yang cocok."
                />

                <TableFooter
                    from={interns.from}
                    to={interns.to}
                    total={interns.total}
                    links={interns.links}
                />
            </TableCard>

            <ConfirmDeleteDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Hapus peserta magang ini?"
                description={
                    <>
                        Akun pengguna dan seluruh data absensi{" "}
                        <span className="font-semibold text-foreground">
                            {deleting?.user?.name}
                        </span>{" "}
                        akan dihapus permanen.
                    </>
                }
                deleteUrl={
                    deleting
                        ? route("admin.interns.destroy", deleting.id)
                        : null
                }
            />
        </AuthenticatedLayout>
    );
}
