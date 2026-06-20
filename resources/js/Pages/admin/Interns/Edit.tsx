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
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Peserta Magang
                </h1>
            }
        >
            <Head title="Ubah Peserta Magang" />

            <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm sm:p-8">
                <InternForm programs={programs} intern={intern} />
            </div>
        </AuthenticatedLayout>
    );
}
