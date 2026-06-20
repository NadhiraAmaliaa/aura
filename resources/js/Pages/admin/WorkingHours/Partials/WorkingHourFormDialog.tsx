import Checkbox from "@/Components/Checkbox";
import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import { dayOfWeekLabels } from "@/lib/labels";
import { WorkingHour } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface WorkingHourFormData {
    is_working_day: boolean;
    start_time: string;
    end_time: string;
    [key: string]: string | boolean;
}

function initialData(workingHour?: WorkingHour): WorkingHourFormData {
    return {
        is_working_day: workingHour?.is_working_day ?? true,
        start_time: workingHour?.start_time ?? "",
        end_time: workingHour?.end_time ?? "",
    };
}

export default function WorkingHourFormDialog({
    workingHour,
    open,
    onOpenChange,
}: {
    workingHour: WorkingHour | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { data, setData, put, processing, errors, clearErrors } =
        useForm<WorkingHourFormData>(initialData(workingHour ?? undefined));

    useEffect(() => {
        if (open) {
            setData(initialData(workingHour ?? undefined));
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (!workingHour) {
            return;
        }

        put(route("admin.working-hours.update", workingHour.id), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    const dayLabel = workingHour
        ? (dayOfWeekLabels[workingHour.day_of_week] ??
          String(workingHour.day_of_week))
        : "";

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={`Ubah Jam Kerja — ${dayLabel}`}
            description="Atur status hari kerja dan jam absensi untuk hari ini."
            onSubmit={submit}
            processing={processing}
            submitLabel="Simpan Perubahan"
        >
            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_working_day}
                    onChange={(event) =>
                        setData("is_working_day", event.target.checked)
                    }
                />
                <span className="text-sm text-gray-700">
                    Hari kerja (peserta dapat melakukan absensi pada hari ini)
                </span>
            </label>
            <InputError message={errors.is_working_day} />

            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="start_time" value="Jam Masuk" />
                    <TextInput
                        id="start_time"
                        type="time"
                        className="mt-1 block w-full"
                        value={data.start_time}
                        disabled={!data.is_working_day}
                        onChange={(event) =>
                            setData("start_time", event.target.value)
                        }
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
                        onChange={(event) =>
                            setData("end_time", event.target.value)
                        }
                    />
                    <InputError className="mt-2" message={errors.end_time} />
                </div>
            </div>
        </FormDialog>
    );
}
