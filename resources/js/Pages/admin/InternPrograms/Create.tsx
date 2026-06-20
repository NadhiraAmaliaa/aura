import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import InternProgramFormDialog from "./Partials/InternProgramFormDialog";

/**
 * Standalone create route. Reuses the index dialog so the route stays functional.
 */
export default function Create() {
    const backToIndex = () =>
        router.visit(route("admin.intern-programs.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Program Magang
                </h1>
            }
        >
            <Head title="Tambah Program Magang" />

            <InternProgramFormDialog
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
