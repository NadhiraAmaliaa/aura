import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { StudyProgram } from "@/types";
import { Head, router } from "@inertiajs/react";
import StudyProgramFormDialog from "./Partials/StudyProgramFormDialog";

/**
 * Standalone edit route. Reuses the index dialog so the route stays functional.
 */
export default function Edit({ studyProgram }: { studyProgram: StudyProgram }) {
    const backToIndex = () => router.visit(route("admin.study-programs.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Program Studi
                </h1>
            }
        >
            <Head title="Ubah Program Studi" />

            <StudyProgramFormDialog
                studyProgram={studyProgram}
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
