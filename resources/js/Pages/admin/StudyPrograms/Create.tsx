import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { University } from "@/types";
import { Head } from "@inertiajs/react";
import StudyProgramForm from "./StudyProgramForm";

export default function Create({
    university,
}: {
    university?: University | null;
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Program Studi
                </h2>
            }
        >
            <Head title="Tambah Program Studi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <StudyProgramForm university={university} />
            </div>
        </AuthenticatedLayout>
    );
}
