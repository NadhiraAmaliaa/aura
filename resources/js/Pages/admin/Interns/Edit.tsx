import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Intern, InternProgram } from '@/types';
import { Head } from '@inertiajs/react';
import InternForm from './InternForm';

export default function Edit({
    intern,
    programs,
}: {
    intern: Intern;
    programs: InternProgram[];
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Peserta Magang
                </h2>
            }
        >
            <Head title="Ubah Peserta Magang" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <InternForm programs={programs} intern={intern} />
            </div>
        </AuthenticatedLayout>
    );
}
