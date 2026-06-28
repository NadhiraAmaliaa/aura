import DataTable, { Column } from "@/Components/admin/DataTable";
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
import { LeaveRequest, LeaveStatus, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { FormEventHandler, useMemo, useState } from "react";

interface Filters {
    status?: string;
    type?: string;
    search?: string;
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
    filters,
    perPage,
}: {
    leaveRequests: Paginated<LeaveRequest>;
    filters: Filters;
    perPage: number;
}) {
    const [form, setForm] = useState<Filters>({
        status: filters.status ?? "",
        type: filters.type ?? "",
        search: filters.search ?? "",
    });

    const [tableSearch, setTableSearch] = useState("");

    const rows = useMemo(() => {
        const term = tableSearch.trim().toLowerCase();
        if (!term) return leaveRequests.data;

        return leaveRequests.data.filter((leave) =>
            [
                leave.request_number,
                leave.user?.name,
                leave.user?.intern?.nim,
                leaveTypeLabels[leave.type],
                `${formatDate(leave.start_date)} - ${formatDate(leave.end_date)}`,
                formatDate(leave.created_at),
                leaveStatusLabels[leave.status],
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [leaveRequests.data, tableSearch]);

    const changePerPage = (value: number) => {
        router.get(
            route("admin.leave-requests.index"),
            { ...form, perPage: value },
            { preserveState: true, replace: true },
        );
    };

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

    const applyFilters: FormEventHandler = (event) => {
        event.preventDefault();
        router.get(
            route("admin.leave-requests.index"),
            { ...form, perPage },
            { preserveState: true, replace: true },
        );
    };

    const resetFilters = () => {
        setForm({
            status: "",
            type: "",
            search: "",
        });
        router.get(
            route("admin.leave-requests.index"),
            {},
            { preserveState: true, replace: true },
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
                onSubmit={applyFilters}
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
                <FilterField label="Status" htmlFor="status">
                    <FilterSelect
                        id="status"
                        value={form.status}
                        options={statusOptions}
                        placeholder="Semua Status"
                        onChange={(val) => setForm({ ...form, status: val })}
                    />
                </FilterField>

                <FilterField label="Jenis" htmlFor="type">
                    <FilterSelect
                        id="type"
                        value={form.type}
                        options={typeOptions}
                        placeholder="Semua Jenis"
                        onChange={(val) => setForm({ ...form, type: val })}
                    />
                </FilterField>

                <FilterField
                    label="Cari nomor / nama"
                    htmlFor="search"
                    className="col-span-2"
                >
                    <input
                        id="search"
                        type="text"
                        placeholder="Ketik lalu tekan Enter..."
                        value={form.search}
                        onChange={(event) =>
                            setForm({ ...form, search: event.target.value })
                        }
                        className={filterControlClass}
                    />
                </FilterField>
            </FilterCard>

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
