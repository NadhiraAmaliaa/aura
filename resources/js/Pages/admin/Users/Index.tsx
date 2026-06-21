import ActionButton from "@/Components/admin/ActionButton";
import DataTable, { Column } from "@/Components/admin/DataTable";
import PageHeader from "@/Components/admin/PageHeader";
import RowActions, { IconAction } from "@/Components/admin/RowActions";
import StatusBadge from "@/Components/admin/StatusBadge";
import TableCard from "@/Components/admin/TableCard";
import TableFooter from "@/Components/admin/TableFooter";
import TableToolbar from "@/Components/admin/TableToolbar";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { ManagedUser, Paginated } from "@/types";
import { Head, router } from "@inertiajs/react";
import { useMemo, useState } from "react";

function roleLabel(role: ManagedUser["role"]): string {
    return role === "admin" ? "Admin" : "Supervisor";
}

export default function Index({
    users,
    perPage,
}: {
    users: Paginated<ManagedUser>;
    perPage: number;
}) {
    const [search, setSearch] = useState("");

    const rows = useMemo(() => {
        const term = search.trim().toLowerCase();

        if (!term) {
            return users.data;
        }

        return users.data.filter((user) =>
            [
                user.name,
                user.nik ?? "",
                user.nik ?? "",
                roleLabel(user.role),
                user.division?.name ?? "",
                user.is_active ? "Aktif" : "Nonaktif",
            ]
                .join(" ")
                .toLowerCase()
                .includes(term),
        );
    }, [users.data, search]);

    const changePerPage = (value: number) => {
        router.get(
            route("admin.users.index"),
            { perPage: value },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    const columns: Column<ManagedUser>[] = [
        {
            header: "Nama",
            cell: (user) => (
                <span className="font-semibold text-on-surface">
                    {user.name}
                </span>
            ),
        },
        {
            header: "NIK",
            cell: (user) => user.nik ?? "-",
        },
        {
            header: "Username",
            cell: (user) => user.nik ?? "-",
        },
        {
            header: "Role",
            cell: (user) => (
                <StatusBadge tone={user.role === "admin" ? "info" : "neutral"}>
                    {roleLabel(user.role)}
                </StatusBadge>
            ),
        },
        {
            header: "Divisi",
            cell: (user) => user.division?.name ?? "-",
        },
        {
            header: "Status",
            cell: (user) => (
                <StatusBadge tone={user.is_active ? "success" : "neutral"}>
                    {user.is_active ? "Aktif" : "Nonaktif"}
                </StatusBadge>
            ),
        },
        {
            header: "Aksi",
            align: "center",
            cell: (user) => (
                <RowActions>
                    <IconAction
                        icon="edit"
                        label="Ubah akun"
                        tone="edit"
                        href={route("admin.users.edit", user.id)}
                    />
                </RowActions>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <PageHeader title="Manajemen User">
                    <ActionButton
                        label="Tambah"
                        href={route("admin.users.create")}
                    />
                </PageHeader>
            }
        >
            <Head title="Manajemen User" />

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
                    getRowKey={(user) => user.id}
                    emptyIcon="manage_accounts"
                    emptyText={
                        search
                            ? "Tidak ada akun yang cocok."
                            : "Belum ada akun."
                    }
                />

                <TableFooter
                    from={users.from}
                    to={users.to}
                    total={users.total}
                    links={users.links}
                />
            </TableCard>
        </AuthenticatedLayout>
    );
}
