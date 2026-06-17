import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { AttendanceLocation } from "@/types";
import { Head } from "@inertiajs/react";
import AttendanceLocationForm from "./AttendanceLocationForm";

export default function Edit({ location }: { location: AttendanceLocation }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Lokasi Absensi
                </h2>
            }
        >
            <Head title="Ubah Lokasi Absensi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <AttendanceLocationForm location={location} />
            </div>
        </AuthenticatedLayout>
    );
}
