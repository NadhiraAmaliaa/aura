import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import UniversityForm from "./UniversityForm";

export default function Create() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Perguruan Tinggi
                </h2>
            }
        >
            <Head title="Tambah Perguruan Tinggi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <UniversityForm />
            </div>
        </AuthenticatedLayout>
    );
}
