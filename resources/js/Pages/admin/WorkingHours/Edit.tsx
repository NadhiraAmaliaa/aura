import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { dayOfWeekLabels } from "@/lib/labels";
import { WorkingHour } from "@/types";
import { Head } from "@inertiajs/react";
import WorkingHourForm from "./WorkingHourForm";

export default function Edit({ workingHour }: { workingHour: WorkingHour }) {
    const dayLabel =
        dayOfWeekLabels[workingHour.day_of_week] ?? workingHour.day_of_week;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ubah Jam Kerja — {dayLabel}
                </h2>
            }
        >
            <Head title={`Ubah Jam Kerja — ${dayLabel}`} />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <WorkingHourForm workingHour={workingHour} />
            </div>
        </AuthenticatedLayout>
    );
}
