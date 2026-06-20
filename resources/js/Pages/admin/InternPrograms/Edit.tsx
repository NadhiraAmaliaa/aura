import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { InternProgram } from "@/types";
import { Head, router } from "@inertiajs/react";
import InternProgramFormDialog from "./Partials/InternProgramFormDialog";

/**
 * Standalone edit route. Reuses the index dialog so the route stays functional.
 */
export default function Edit({ program }: { program: InternProgram }) {
    const backToIndex = () =>
        router.visit(route("admin.intern-programs.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Program Magang
                </h1>
            }
        >
            <Head title="Ubah Program Magang" />

            <InternProgramFormDialog
                program={program}
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
