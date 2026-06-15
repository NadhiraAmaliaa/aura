import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { NonWorkingDay } from '@/types';
import { Head } from '@inertiajs/react';
import NonWorkingDayForm from './NonWorkingDayForm';

export default function Edit({
    nonWorkingDay,
}: {
    nonWorkingDay: NonWorkingDay;
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Hari Libur
                </h2>
            }
        >
            <Head title="Ubah Hari Libur" />

            <div className="max-w-xl rounded-lg bg-white p-6 shadow sm:p-8">
                <NonWorkingDayForm nonWorkingDay={nonWorkingDay} />
            </div>
        </AuthenticatedLayout>
    );
}
