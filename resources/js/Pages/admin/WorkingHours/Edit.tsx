import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { WorkingHour } from "@/types";
import { Head, router } from "@inertiajs/react";
import WorkingHourFormDialog from "./Partials/WorkingHourFormDialog";

/**
 * Standalone edit route. Reuses the index dialog so the route stays functional.
 */
export default function Edit({ workingHour }: { workingHour: WorkingHour }) {
    const backToIndex = () => router.visit(route("admin.working-hours.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Jam Kerja
                </h1>
            }
        >
            <Head title="Ubah Jam Kerja" />

            <WorkingHourFormDialog
                workingHour={workingHour}
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
