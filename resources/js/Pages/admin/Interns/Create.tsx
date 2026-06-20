import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { InternProgram } from '@/types';
import { Head } from '@inertiajs/react';
import InternForm from './InternForm';

export default function Create({ programs }: { programs: InternProgram[] }) {
    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Peserta Magang
                </h1>
            }
        >
            <Head title="Tambah Peserta Magang" />

            <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm sm:p-8">
                <InternForm programs={programs} />
            </div>
        </AuthenticatedLayout>
    );
}
