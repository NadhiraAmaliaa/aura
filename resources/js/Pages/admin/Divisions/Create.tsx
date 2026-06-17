import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import DivisionForm from "./DivisionForm";

export default function Create() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Divisi
                </h2>
            }
        >
            <Head title="Tambah Divisi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <DivisionForm />
            </div>
        </AuthenticatedLayout>
    );
}
