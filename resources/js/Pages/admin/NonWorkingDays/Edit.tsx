import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { NonWorkingDay } from "@/types";
import { Head, router } from "@inertiajs/react";
import NonWorkingDayFormDialog from "./Partials/NonWorkingDayFormDialog";

/**
 * Standalone edit route. Reuses the index dialog so the route stays functional.
 */
export default function Edit({
    nonWorkingDay,
}: {
    nonWorkingDay: NonWorkingDay;
}) {
    const backToIndex = () =>
        router.visit(route("admin.non-working-days.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Hari Libur
                </h1>
            }
        >
            <Head title="Ubah Hari Libur" />

            <NonWorkingDayFormDialog
                nonWorkingDay={nonWorkingDay}
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
