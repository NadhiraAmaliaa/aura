import ActionButton from "@/Components/admin/ActionButton";
import ConfirmActionDialog from "@/Components/admin/ConfirmActionDialog";
import DataTable, { Column } from "@/Components/admin/DataTable";
import DatePicker from "@/Components/admin/DatePicker";
import { FilterField, filterControlClass } from "@/Components/admin/FilterCard";
import FilterSelect from "@/Components/admin/FilterSelect";
import MaterialIcon from "@/Components/MaterialIcon";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import {
    formatDate,
    internStatusBadgeTone,
    internStatusLabels,
} from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division, Intern, InternProgram, PageProps, Paginated } from "@/types";
import { Head, router, usePage } from "@inertiajs/react";
import { FormEventHandler, useMemo, useState } from "react";

interface Filters {
    search: string;
    program: number | null;
    division: number | null;
    status: string;
    period_from: string | null;
    period_to: string | null;
}

type Tab = "data" | "arsip";

export default function Index({
    interns,
    programs,
    divisions,
    filters,
    perPage,
    tab,
    tabCounts,
}: {
    interns: Paginated<Intern>;
    programs: Pick<InternProgram, "id" | "name">[];
    divisions: Pick<Division, "id" | "name">[];
    filters: Filters;
    perPage: number;
    tab: Tab;
    tabCounts: Record<Tab, number>;
}) {
    const isAdmin = usePage<PageProps>().props.auth.user?.is_admin ?? false;
    const isArchive = tab === "arsip";
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

    const [archiving, setArchiving] = useState<Intern | null>(null);
    const [restoring, setRestoring] = useState<Intern | null>(null);

    const [tableSearch, setTableSearch] = useState("");

    const periodText = (intern: Intern) => {
        if (!intern.start_date && !intern.end_date) {
            return "-";
        }

        return `${formatDate(intern.start_date)} - ${formatDate(intern.end_date)}`;
    };

    const rows = useMemo(() => {
        const term = tableSearch.trim().toLowerCase();
        if (!term) return interns.data;

        return interns.data.filter((intern) =>
            [
                intern.user?.name,
                intern.nim,
                intern.university_ref?.name ?? intern.university,
                intern.study_program?.name ?? intern.major,
                intern.division_ref?.name ?? intern.division,
                intern.intern_program?.name,
                periodText(intern),
                internStatusLabels[intern.effective_status],
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [interns.data, tableSearch]);

    const changePerPage = (value: number) => {
        applyFilters({ perPage: String(value) });
    };

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
                tab: next.tab ?? tab,
                search: next.search ?? search,
                program: next.program ?? program,
                division: next.division ?? division,
                status: next.status ?? status,
                period_from: next.period_from ?? periodFrom,
                period_to: next.period_to ?? periodTo,
                perPage: next.perPage ?? String(perPage),
            },
            { preserveState: true, preserveScroll: true, replace: true },
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
            { tab },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const openArchive = (intern: Intern) => setArchiving(intern);
    const openRestore = (intern: Intern) => setRestoring(intern);

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
                <StatusBadge
                    tone={internStatusBadgeTone[intern.effective_status]}
                >
                    {internStatusLabels[intern.effective_status]}
                </StatusBadge>
            ),
        },
        ...(isAdmin
            ? [
                  {
                      header: "Aksi",
                      align: "center" as const,
                      cell: (intern: Intern) => (
                          <RowActions>
                              {isArchive ? (
                                  <IconAction
                                      icon="restore_from_trash"
                                      label="Pulihkan peserta"
                                      tone="restore"
                                      onClick={() => openRestore(intern)}
                                  />
                              ) : (
                                  <>
                                      <IconAction
                                          icon="edit"
                                          label="Ubah peserta"
                                          tone="edit"
                                          href={route(
                                              "admin.interns.edit",
                                              intern.id,
                                          )}
                                      />
                                      <IconAction
                                          icon="archive"
                                          label="Arsipkan peserta"
                                          tone="archive"
                                          onClick={() => openArchive(intern)}
                                      />
                                  </>
                              )}
                          </RowActions>
                      ),
                  },
              ]
            : []),
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Peserta Magang">
                    {isAdmin && (
                        <ActionButton
                            label="Tambah"
                            href={route("admin.interns.create")}
                        />
                    )}
                </PageHeader>
            }
        >
            <Head title="Peserta Magang" />

            <form
                onSubmit={submitFilters}
                className="mb-6 rounded-xl border border-outline-variant bg-white p-4 shadow-sm"
            >
                <div className="flex flex-col gap-4">
                    {/* Row 1: dropdown filters */}
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
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

                        {isAdmin && (
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
                        )}

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

                        <FilterField
                            label="Periode mulai dari"
                            htmlFor="period_from"
                        >
                            <DatePicker
                                id="period_from"
                                value={periodFrom}
                                onChange={(val) => {
                                    const nextPeriodTo =
                                        periodTo && val && periodTo < val
                                            ? ""
                                            : periodTo;
                                    setPeriodFrom(val);
                                    if (nextPeriodTo !== periodTo) {
                                        setPeriodTo(nextPeriodTo);
                                    }
                                    applyFilters({
                                        period_from: val,
                                        period_to: nextPeriodTo,
                                    });
                                }}
                            />
                        </FilterField>

                        <FilterField label="Periode sampai" htmlFor="period_to">
                            <DatePicker
                                id="period_to"
                                value={periodTo}
                                min={periodFrom}
                                onChange={(val) => {
                                    setPeriodTo(val);
                                    applyFilters({ period_to: val });
                                }}
                            />
                        </FilterField>
                    </div>

                    {/* Row 2: search + buttons */}
                    <div className="flex items-end gap-3">
                        <div className="flex flex-1 flex-col">
                            <label
                                htmlFor="search"
                                className="mb-1 block text-xs font-medium text-on-surface-variant"
                            >
                                Cari nama atau NIM
                            </label>
                            <input
                                id="search"
                                type="text"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Ketik lalu tekan Enter..."
                                className={filterControlClass}
                            />
                        </div>
                        <button
                            type="submit"
                            className="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary/90"
                        >
                            <MaterialIcon
                                name="search"
                                style={{ fontSize: 18 }}
                            />
                            Filter
                        </button>
                        <button
                            type="button"
                            onClick={resetFilters}
                            className="inline-flex h-10 shrink-0 items-center gap-1.5 rounded-lg border border-outline-variant px-4 text-sm font-medium text-on-surface-variant transition hover:border-primary/50 hover:text-on-surface"
                        >
                            <MaterialIcon
                                name="restart_alt"
                                style={{ fontSize: 18 }}
                            />
                            Reset
                        </button>
                    </div>
                </div>
            </form>

            <div className="mb-4 flex gap-1 border-b border-outline-variant">
                {(
                    [
                        { key: "data", label: "Data Peserta" },
                        { key: "arsip", label: "Arsip" },
                    ] as { key: Tab; label: string }[]
                ).map(({ key, label }) => {
                    const active = tab === key;

                    return (
                        <button
                            key={key}
                            type="button"
                            onClick={() => applyFilters({ tab: key })}
                            className={
                                "-mb-px flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold transition " +
                                (active
                                    ? "border-primary text-primary"
                                    : "border-transparent text-on-surface-variant hover:text-on-surface")
                            }
                        >
                            {label}
                            <span
                                className={
                                    "rounded-full px-2 py-0.5 text-xs font-semibold " +
                                    (active
                                        ? "bg-primary/10 text-primary"
                                        : "bg-surface-container text-on-surface-variant")
                                }
                            >
                                {tabCounts[key]}
                            </span>
                        </button>
                    );
                })}
            </div>

            <TableCard>
                <TableToolbar
                    search={tableSearch}
                    onSearchChange={setTableSearch}
                    perPage={perPage}
                    onPerPageChange={changePerPage}
                />

                <DataTable
                    columns={columns}
                    rows={rows}
                    getRowKey={(intern) => intern.id}
                    emptyIcon="groups"
                    emptyText={
                        isArchive
                            ? "Belum ada peserta magang yang diarsipkan."
                            : "Tidak ada peserta magang yang cocok."
                    }
                />

                <TableFooter
                    from={interns.from}
                    to={interns.to}
                    total={interns.total}
                    links={interns.links}
                />
            </TableCard>

            <ConfirmActionDialog
                open={archiving !== null}
                onOpenChange={(open) => !open && setArchiving(null)}
                title="Arsipkan peserta magang ini?"
                description={
                    <>
                        <span className="font-semibold text-foreground">
                            {archiving?.user?.name}
                        </span>{" "}
                        akan dipindahkan ke arsip. Riwayat absensi, pengajuan
                        izin, serta laporan tetap tersimpan dan peserta dapat
                        dipulihkan kapan saja.
                    </>
                }
                url={
                    archiving
                        ? route("admin.interns.destroy", archiving.id)
                        : null
                }
                method="delete"
                confirmLabel="Arsipkan"
            />

            <ConfirmActionDialog
                open={restoring !== null}
                onOpenChange={(open) => !open && setRestoring(null)}
                title="Pulihkan peserta magang ini?"
                description={
                    <>
                        <span className="font-semibold text-foreground">
                            {restoring?.user?.name}
                        </span>{" "}
                        akan dikembalikan ke daftar peserta aktif.
                    </>
                }
                url={
                    restoring
                        ? route("admin.interns.restore", restoring.id)
                        : null
                }
                method="patch"
                confirmLabel="Pulihkan"
            />
        </AuthenticatedLayout>
    );
}
