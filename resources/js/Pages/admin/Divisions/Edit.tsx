import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division } from "@/types";
import { Head } from "@inertiajs/react";
import DivisionForm from "./DivisionForm";

export default function Edit({ division }: { division: Division }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Divisi
                </h2>
            }
        >
            <Head title="Ubah Divisi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <DivisionForm division={division} />
            </div>
        </AuthenticatedLayout>
    );
}
