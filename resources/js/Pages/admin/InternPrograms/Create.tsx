import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import InternProgramForm from "./InternProgramForm";

export default function Create() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Program Magang
                </h2>
            }
        >
            <Head title="Tambah Program Magang" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <InternProgramForm />
            </div>
        </AuthenticatedLayout>
    );
}
