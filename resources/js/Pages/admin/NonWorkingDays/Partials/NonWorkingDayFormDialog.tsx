import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import { nonWorkingDayTypeLabels } from "@/lib/labels";
import { NonWorkingDay, NonWorkingDayType } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface NonWorkingDayFormData {
    date: string;
    name: string;
    type: NonWorkingDayType;
    [key: string]: string;
}

function initialData(day?: NonWorkingDay): NonWorkingDayFormData {
    return {
        date: day?.date ?? "",
        name: day?.name ?? "",
        type: day?.type ?? "national_holiday",
    };
}

export default function NonWorkingDayFormDialog({
    nonWorkingDay,
    open,
    onOpenChange,
}: {
    nonWorkingDay?: NonWorkingDay;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEdit = Boolean(nonWorkingDay);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<NonWorkingDayFormData>(initialData(nonWorkingDay));

    useEffect(() => {
        if (open) {
            setData(initialData(nonWorkingDay));
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (isEdit && nonWorkingDay) {
            put(
                route("admin.non-working-days.update", nonWorkingDay.id),
                options,
            );
        } else {
            post(route("admin.non-working-days.store"), options);
        }
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? "Ubah Hari Libur" : "Tambah Hari Libur"}
            description="Hari libur menonaktifkan absensi pada tanggal terkait."
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? "Simpan Perubahan" : "Tambah Hari Libur"}
        >
            <div>
                <InputLabel htmlFor="date" value="Tanggal" />
                <TextInput
                    id="date"
                    type="date"
                    className="mt-1 block w-full"
                    value={data.date}
                    isFocused
                    onChange={(event) => setData("date", event.target.value)}
                />
                <InputError className="mt-2" message={errors.date} />
            </div>

            <div>
                <InputLabel htmlFor="name" value="Nama Hari Libur" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(event) => setData("name", event.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel htmlFor="type" value="Jenis" />
                <SelectInput
                    id="type"
                    className="mt-1 block w-full"
                    value={data.type}
                    onChange={(event) =>
                        setData("type", event.target.value as NonWorkingDayType)
                    }
                >
                    {Object.entries(nonWorkingDayTypeLabels).map(
                        ([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ),
                    )}
                </SelectInput>
                <InputError className="mt-2" message={errors.type} />
            </div>
        </FormDialog>
    );
}
