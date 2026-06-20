import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division } from "@/types";
import { Head, router } from "@inertiajs/react";
import DivisionFormDialog from "./Partials/DivisionFormDialog";

/**
 * Standalone edit route. Reuses the index dialog so the route stays functional.
 */
export default function Edit({ division }: { division: Division }) {
    const backToIndex = () => router.visit(route("admin.divisions.index"));

    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Divisi
                </h1>
            }
        >
            <Head title="Ubah Divisi" />

            <DivisionFormDialog
                division={division}
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
