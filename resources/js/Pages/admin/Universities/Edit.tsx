import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { University } from "@/types";
import { Head, router } from "@inertiajs/react";
import UniversityFormDialog from "./Partials/UniversityFormDialog";

/**
 * Standalone edit route. Reuses the index dialog so the route stays functional.
 */
export default function Edit({ university }: { university: University }) {
    const backToIndex = () => router.visit(route("admin.universities.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Perguruan Tinggi
                </h1>
            }
        >
            <Head title="Ubah Perguruan Tinggi" />

            <UniversityFormDialog
                university={university}
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
