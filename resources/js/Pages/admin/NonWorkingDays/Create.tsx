import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import NonWorkingDayForm from './NonWorkingDayForm';

export default function Create() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Hari Libur
                </h2>
            }
        >
            <Head title="Tambah Hari Libur" />

            <div className="max-w-xl rounded-lg bg-white p-6 shadow sm:p-8">
                <NonWorkingDayForm />
            </div>
        </AuthenticatedLayout>
    );
}
