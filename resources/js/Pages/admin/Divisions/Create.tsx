import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import DivisionFormDialog from "./Partials/DivisionFormDialog";

/**
 * Standalone create route. The primary entry point is the dialog on the index
 * page; this page reuses the same dialog so the route stays functional.
 */
export default function Create() {
    const backToIndex = () => router.visit(route("admin.divisions.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Divisi
                </h1>
            }
        >
            <Head title="Tambah Divisi" />

            <DivisionFormDialog
                open
                onOpenChange={(value) => {
                    if (!value) {
                        backToIndex();
                    }
                }}
            />
        </AuthenticatedLayout>
    );
}
