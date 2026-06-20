import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import UniversityFormDialog from "./Partials/UniversityFormDialog";

/**
 * Standalone create route. Reuses the index dialog so the route stays functional.
 */
export default function Create() {
    const backToIndex = () => router.visit(route("admin.universities.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Perguruan Tinggi
                </h1>
            }
        >
            <Head title="Tambah Perguruan Tinggi" />

            <UniversityFormDialog
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
