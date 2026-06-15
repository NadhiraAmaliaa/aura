import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { InternProgram } from '@/types';
import { Head } from '@inertiajs/react';
import InternForm from './InternForm';

export default function Create({ programs }: { programs: InternProgram[] }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Peserta Magang
                </h2>
            }
        >
            <Head title="Tambah Peserta Magang" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <InternForm programs={programs} />
            </div>
        </AuthenticatedLayout>
    );
}
