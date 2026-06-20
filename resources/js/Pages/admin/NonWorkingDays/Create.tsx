import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import NonWorkingDayFormDialog from "./Partials/NonWorkingDayFormDialog";

/**
 * Standalone create route. Reuses the index dialog so the route stays functional.
 */
export default function Create() {
    const backToIndex = () =>
        router.visit(route("admin.non-working-days.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Hari Libur
                </h1>
            }
        >
            <Head title="Tambah Hari Libur" />

            <NonWorkingDayFormDialog
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
