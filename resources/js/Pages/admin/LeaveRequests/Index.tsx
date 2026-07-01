import DataTable, { Column } from "@/Components/admin/DataTable";
import DatePicker from "@/Components/admin/DatePicker";
import FilterCard, {
    FilterField,
    filterControlClass,
} from "@/Components/admin/FilterCard";
import FilterSelect from "@/Components/admin/FilterSelect";
import MaterialIcon from "@/Components/MaterialIcon";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import { formatDate, leaveStatusLabels, leaveTypeLabels } from "@/lib/labels";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
    Division,
    LeaveRequest,
    LeaveStatus,
    PageProps,
    Paginated,
} from "@/types";
import { Head, router, usePage } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

interface Filters {
    status: string;
    type: string;
    search: string;
    division: number | null;
    period_start: string | null;
    period_end: string | null;
    submitted_on: string | null;
    perPage: number | null;
}

const statusTone: Record<
    LeaveStatus,
    "success" | "neutral" | "info" | "warning" | "danger"
> = {
    pending: "warning",
    approved: "success",
    rejected: "danger",
};

export default function Index({
    leaveRequests,
    divisions,
    filters,
}: {
    leaveRequests: Paginated<LeaveRequest>;
    divisions: Pick<Division, "id" | "name">[];
    filters: Filters;
}) {
    const isAdmin = usePage<PageProps>().props.auth.user?.is_admin ?? false;

    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [type, setType] = useState(filters.type ?? "");
    const [division, setDivision] = useState(
        filters.division ? String(filters.division) : "",
    );
    const [periodStart, setPeriodStart] = useState(filters.period_start ?? "");
    const [periodEnd, setPeriodEnd] = useState(filters.period_end ?? "");
    const [submittedOn, setSubmittedOn] = useState(filters.submitted_on ?? "");
    const [perPage, setPerPage] = useState<number | null>(
        filters.perPage ?? null,
    );

    const statusOptions = Object.entries(leaveStatusLabels).map(
        ([value, label]) => ({
            value,
            label,
        }),
    );

    const typeOptions = Object.entries(leaveTypeLabels).map(
        ([value, label]) => ({
            value,
            label,
        }),
    );

    const divisionOptions = divisions.map((d) => ({
        value: String(d.id),
        label: d.name,
    }));

    const applyFilters = (next: Partial<Record<string, string>>) => {
        router.get(
            route("admin.leave-requests.index"),
            {
                search: next.search ?? search,
                status: next.status ?? status,
                type: next.type ?? type,
                division: next.division ?? division,
                period_start: next.period_start ?? periodStart,
                period_end: next.period_end ?? periodEnd,
                submitted_on: next.submitted_on ?? submittedOn,
                perPage:
                    next.perPage ?? (perPage !== null ? String(perPage) : ""),
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
        setStatus("");
        setType("");
        setDivision("");
        setPeriodStart("");
        setPeriodEnd("");
        setSubmittedOn("");
        setPerPage(null);
        router.get(
            route("admin.leave-requests.index"),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    // Per-page changes keep the currently applied filters (from the server),
    // not any pending edits that haven't been submitted via the Filter button.
    const handlePerPageChange = (value: number | null) => {
        setPerPage(value);
        router.get(
            route("admin.leave-requests.index"),
            {
                search: filters.search ?? "",
                status: filters.status ?? "",
                type: filters.type ?? "",
                division: filters.division ? String(filters.division) : "",
                period_start: filters.period_start ?? "",
                period_end: filters.period_end ?? "",
                submitted_on: filters.submitted_on ?? "",
                perPage: value !== null ? String(value) : "",
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const columns: Column<LeaveRequest>[] = [
        {
            header: "Nomor",
            cell: (leave) => (
                <span className="font-mono text-sm font-bold text-on-surface">
                    {leave.request_number}
                </span>
            ),
        },
        {
            header: "NIM",
            cell: (leave) => (
                <span className="font-mono text-sm text-on-surface-variant">
                    {leave.user?.intern?.nim ?? "-"}
                </span>
            ),
        },
        {
            header: "Nama",
            cell: (leave) => (
                <span className="font-semibold text-on-surface">
                    {leave.user?.name}
                </span>
            ),
        },
        ...(isAdmin
            ? [
                  {
                      header: "Divisi",
                      cell: (leave: LeaveRequest) =>
                          leave.user?.intern?.division_ref?.name ??
                          leave.user?.intern?.division ??
                          "-",
                  },
              ]
            : []),
        {
            header: "Jenis",
            cell: (leave) => leaveTypeLabels[leave.type],
        },
        {
            header: "Periode",
            className: "whitespace-nowrap",
            cell: (leave) =>
                `${formatDate(leave.start_date)} - ${formatDate(leave.end_date)}`,
        },
        {
            header: "Tgl Pengajuan",
            className: "whitespace-nowrap",
            cell: (leave) => formatDate(leave.created_at),
        },
        {
            header: "Status",
            align: "center",
            cell: (leave) => (
                <StatusBadge tone={statusTone[leave.status]}>
                    {leaveStatusLabels[leave.status]}
                </StatusBadge>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (leave) => (
                <RowActions>
                    <IconAction
                        icon="visibility"
                        label="Lihat detail"
                        tone="view"
                        href={route("admin.leave-requests.show", leave.id)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout header={<PageHeader title="Pengajuan Izin" />}>
            <Head title="Pengajuan Izin" />

            <FilterCard
                onSubmit={submitFilters}
                actions={
                    <>
                        <button
                            type="submit"
                            className="inline-flex h-10 items-center gap-1.5 rounded-lg bg-primary px-5 text-sm font-semibold text-white transition hover:bg-primary/90"
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
                            className="inline-flex h-10 items-center gap-1.5 rounded-lg border border-outline-variant px-4 text-sm font-medium text-on-surface-variant transition hover:border-primary/50 hover:text-on-surface"
                        >
                            <MaterialIcon
                                name="restart_alt"
                                style={{ fontSize: 18 }}
                            />
                            Reset
                        </button>
                    </>
                }
            >
                <FilterField label="Cari nomor / nama / NIM" htmlFor="search">
                    <input
                        id="search"
                        type="text"
                        placeholder="Ketik lalu klik Filter..."
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        className={filterControlClass}
                    />
                </FilterField>

                <FilterField label="Status" htmlFor="status">
                    <FilterSelect
                        id="status"
                        value={status}
                        options={statusOptions}
                        placeholder="Semua Status"
                        onChange={(val) => setStatus(val)}
                    />
                </FilterField>

                <FilterField label="Jenis" htmlFor="type">
                    <FilterSelect
                        id="type"
                        value={type}
                        options={typeOptions}
                        placeholder="Semua Jenis"
                        onChange={(val) => setType(val)}
                    />
                </FilterField>

                {isAdmin && (
                    <FilterField label="Divisi" htmlFor="division">
                        <FilterSelect
                            id="division"
                            value={division}
                            options={divisionOptions}
                            placeholder="Semua Divisi"
                            onChange={(val) => setDivision(val)}
                        />
                    </FilterField>
                )}

                <FilterField label="Periode mulai dari" htmlFor="period_start">
                    <DatePicker
                        id="period_start"
                        value={periodStart}
                        onChange={(val) => setPeriodStart(val)}
                    />
                </FilterField>

                <FilterField label="Periode sampai" htmlFor="period_end">
                    <DatePicker
                        id="period_end"
                        value={periodEnd}
                        min={periodStart}
                        onChange={(val) => setPeriodEnd(val)}
                    />
                </FilterField>

                <FilterField label="Tanggal pengajuan" htmlFor="submitted_on">
                    <DatePicker
                        id="submitted_on"
                        value={submittedOn}
                        onChange={(val) => setSubmittedOn(val)}
                    />
                </FilterField>
            </FilterCard>

            <TableCard>
                <TableToolbar
                    perPage={perPage}
                    onPerPageChange={handlePerPageChange}
                />

                <DataTable
                    columns={columns}
                    rows={leaveRequests.data}
                    getRowKey={(leave) => leave.id}
                    emptyIcon="mail"
                    emptyText="Tidak ada pengajuan."
                />

                <TableFooter
                    from={leaveRequests.from}
                    to={leaveRequests.to}
                    total={leaveRequests.total}
                    links={leaveRequests.links}
                />
            </TableCard>
        </AuthenticatedLayout>
    );
}
