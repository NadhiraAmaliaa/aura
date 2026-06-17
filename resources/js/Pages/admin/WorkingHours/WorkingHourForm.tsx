import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { dayOfWeekLabels } from "@/lib/labels";
import { WorkingHour } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

interface WorkingHourFormData {
    is_working_day: boolean;
    start_time: string;
    end_time: string;
    [key: string]: string | boolean;
}

export default function WorkingHourForm({
    workingHour,
}: {
    workingHour: WorkingHour;
}) {
    const { data, setData, put, processing, errors } =
        useForm<WorkingHourFormData>({
            is_working_day: workingHour.is_working_day,
            start_time: workingHour.start_time ?? "",
            end_time: workingHour.end_time ?? "",
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route("admin.working-hours.update", workingHour.id));
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel value="Hari" />
                <p className="mt-1 text-sm font-medium text-gray-900">
                    {dayOfWeekLabels[workingHour.day_of_week] ??
                        workingHour.day_of_week}
                </p>
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_working_day}
                    onChange={(e) =>
                        setData("is_working_day", e.target.checked)
                    }
                />
                <span className="text-sm text-gray-700">
                    Hari kerja (peserta dapat melakukan absensi pada hari ini)
                </span>
            </label>
            <InputError className="mt-2" message={errors.is_working_day} />

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="start_time" value="Jam Masuk" />
                    <TextInput
                        id="start_time"
                        type="time"
                        className="mt-1 block w-full"
                        value={data.start_time}
                        disabled={!data.is_working_day}
                        onChange={(e) => setData("start_time", e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.start_time} />
                </div>

                <div>
                    <InputLabel htmlFor="end_time" value="Jam Pulang" />
                    <TextInput
                        id="end_time"
                        type="time"
                        className="mt-1 block w-full"
                        value={data.end_time}
                        disabled={!data.is_working_day}
                        onChange={(e) => setData("end_time", e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.end_time} />
                </div>
            </div>

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route("admin.working-hours.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    Simpan Perubahan
                </PrimaryButton>
            </div>
        </form>
    );
}
