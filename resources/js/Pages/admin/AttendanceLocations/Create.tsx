import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import AttendanceLocationForm from "./AttendanceLocationForm";

export default function Create() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Tambah Lokasi Absensi
                </h2>
            }
        >
            <Head title="Tambah Lokasi Absensi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <AttendanceLocationForm />
            </div>
        </AuthenticatedLayout>
    );
}
