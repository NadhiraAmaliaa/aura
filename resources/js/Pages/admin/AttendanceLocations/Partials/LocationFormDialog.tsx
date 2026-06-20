import Checkbox from "@/Components/Checkbox";
import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import { AttendanceLocation } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface LocationFormData {
    name: string;
    latitude: string;
    longitude: string;
    radius: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

function initialData(location?: AttendanceLocation): LocationFormData {
    return {
        name: location?.name ?? "",
        latitude: location?.latitude ?? "",
        longitude: location?.longitude ?? "",
        radius: location ? String(location.radius) : "",
        is_active: location ? location.is_active : true,
    };
}

export default function LocationFormDialog({
    location,
    open,
    onOpenChange,
}: {
    location?: AttendanceLocation;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEdit = Boolean(location);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<LocationFormData>(initialData(location));

    // Reset the form to the current target every time the dialog opens.
    useEffect(() => {
        if (open) {
            setData(initialData(location));
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

        if (isEdit && location) {
            put(
                route("admin.attendance-locations.update", location.id),
                options,
            );
        } else {
            post(route("admin.attendance-locations.store"), options);
        }
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? "Ubah Lokasi Absensi" : "Tambah Lokasi Absensi"}
            description="Tentukan titik koordinat dan radius toleransi untuk validasi absensi peserta."
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? "Simpan Perubahan" : "Tambah Lokasi"}
        >
            <div>
                <InputLabel htmlFor="name" value="Nama Lokasi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(event) => setData("name", event.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="latitude" value="Latitude" />
                    <TextInput
                        id="latitude"
                        type="number"
                        step="any"
                        className="mt-1 block w-full"
                        value={data.latitude}
                        onChange={(event) =>
                            setData("latitude", event.target.value)
                        }
                    />
                    <InputError className="mt-2" message={errors.latitude} />
                </div>

                <div>
                    <InputLabel htmlFor="longitude" value="Longitude" />
                    <TextInput
                        id="longitude"
                        type="number"
                        step="any"
                        className="mt-1 block w-full"
                        value={data.longitude}
                        onChange={(event) =>
                            setData("longitude", event.target.value)
                        }
                    />
                    <InputError className="mt-2" message={errors.longitude} />
                </div>
            </div>

            <div>
                <InputLabel htmlFor="radius" value="Radius (meter)" />
                <TextInput
                    id="radius"
                    type="number"
                    min="1"
                    className="mt-1 block w-full sm:max-w-[12rem]"
                    value={data.radius}
                    onChange={(event) => setData("radius", event.target.value)}
                />
                <InputError className="mt-2" message={errors.radius} />
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_active}
                    onChange={(event) =>
                        setData("is_active", event.target.checked)
                    }
                />
                <span className="text-sm text-gray-700">
                    Lokasi aktif (digunakan untuk validasi absensi)
                </span>
            </label>
            <InputError message={errors.is_active} />
        </FormDialog>
    );
}
