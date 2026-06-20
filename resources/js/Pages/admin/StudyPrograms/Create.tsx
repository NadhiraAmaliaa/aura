import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { University } from "@/types";
import { Head, router } from "@inertiajs/react";
import StudyProgramFormDialog from "./Partials/StudyProgramFormDialog";

/**
 * Standalone create route. Reuses the index dialog so the route stays functional.
 */
export default function Create({
    university,
}: {
    university?: University | null;
}) {
    const backToIndex = () => router.visit(route("admin.study-programs.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Program Studi
                </h1>
            }
        >
            <Head title="Tambah Program Studi" />

            <StudyProgramFormDialog
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
