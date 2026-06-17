import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { University } from "@/types";
import { Head } from "@inertiajs/react";
import UniversityForm from "./UniversityForm";

export default function Edit({ university }: { university: University }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Perguruan Tinggi
                </h2>
            }
        >
            <Head title="Ubah Perguruan Tinggi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <UniversityForm university={university} />
            </div>
        </AuthenticatedLayout>
    );
}
