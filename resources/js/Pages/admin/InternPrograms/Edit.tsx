import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { InternProgram } from "@/types";
import { Head } from "@inertiajs/react";
import InternProgramForm from "./InternProgramForm";

export default function Edit({ program }: { program: InternProgram }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Program Magang
                </h2>
            }
        >
            <Head title="Ubah Program Magang" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <InternProgramForm program={program} />
            </div>
        </AuthenticatedLayout>
    );
}
