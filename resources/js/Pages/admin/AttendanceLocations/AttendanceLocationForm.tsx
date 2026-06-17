import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { AttendanceLocation } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

interface AttendanceLocationFormData {
    name: string;
    latitude: string;
    longitude: string;
    radius: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

export default function AttendanceLocationForm({
    location,
}: {
    location?: AttendanceLocation;
}) {
    const isEdit = Boolean(location);

    const { data, setData, post, put, processing, errors } =
        useForm<AttendanceLocationFormData>({
            name: location?.name ?? "",
            latitude: location?.latitude ?? "",
            longitude: location?.longitude ?? "",
            radius: location ? String(location.radius) : "",
            is_active: location ? location.is_active : true,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && location) {
            put(route("admin.attendance-locations.update", location.id));
        } else {
            post(route("admin.attendance-locations.store"));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="Nama Lokasi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(e) => setData("name", e.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="latitude" value="Latitude" />
                    <TextInput
                        id="latitude"
                        type="number"
                        step="any"
                        className="mt-1 block w-full"
                        value={data.latitude}
                        onChange={(e) => setData("latitude", e.target.value)}
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
                        onChange={(e) => setData("longitude", e.target.value)}
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
                    className="mt-1 block w-full sm:max-w-xs"
                    value={data.radius}
                    onChange={(e) => setData("radius", e.target.value)}
                />
                <InputError className="mt-2" message={errors.radius} />
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_active}
                    onChange={(e) => setData("is_active", e.target.checked)}
                />
                <span className="text-sm text-gray-700">
                    Lokasi aktif (digunakan untuk validasi absensi)
                </span>
            </label>
            <InputError className="mt-2" message={errors.is_active} />

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route("admin.attendance-locations.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? "Simpan Perubahan" : "Tambah Lokasi"}
                </PrimaryButton>
            </div>
        </form>
    );
}
