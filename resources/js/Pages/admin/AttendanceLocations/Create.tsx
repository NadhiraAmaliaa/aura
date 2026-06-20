import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, router } from "@inertiajs/react";
import LocationFormDialog from "./Partials/LocationFormDialog";

/**
 * Standalone create route. The primary entry point is the dialog on the index
 * page; this page reuses the same dialog so the route stays functional.
 */
export default function Create() {
    const backToIndex = () =>
        router.visit(route("admin.attendance-locations.index"));

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-2xl font-extrabold tracking-tight text-gray-900">
                    Tambah Lokasi Absensi
                </h2>
            }
        >
            <Head title="Tambah Lokasi Absensi" />

            <LocationFormDialog
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
