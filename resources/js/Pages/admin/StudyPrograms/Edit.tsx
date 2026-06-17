import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { StudyProgram } from "@/types";
import { Head } from "@inertiajs/react";
import StudyProgramForm from "./StudyProgramForm";

export default function Edit({ studyProgram }: { studyProgram: StudyProgram }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Program Studi
                </h2>
            }
        >
            <Head title="Ubah Program Studi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <StudyProgramForm studyProgram={studyProgram} />
            </div>
        </AuthenticatedLayout>
    );
}
