import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { University } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

interface UniversityFormData {
    name: string;
    lldikti: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

export default function UniversityForm({
    university,
}: {
    university?: University;
}) {
    const isEdit = Boolean(university);

    const { data, setData, post, put, processing, errors } =
        useForm<UniversityFormData>({
            name: university?.name ?? "",
            lldikti: university?.lldikti ?? "",
            is_active: university ? (university.is_active ?? true) : true,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && university) {
            put(route("admin.universities.update", university.id));
        } else {
            post(route("admin.universities.store"));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="Nama Perguruan Tinggi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(e) => setData("name", e.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel htmlFor="lldikti" value="LLDikti (opsional)" />
                <TextInput
                    id="lldikti"
                    className="mt-1 block w-full"
                    value={data.lldikti}
                    onChange={(e) => setData("lldikti", e.target.value)}
                />
                <InputError className="mt-2" message={errors.lldikti} />
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_active}
                    onChange={(e) => setData("is_active", e.target.checked)}
                />
                <span className="text-sm text-gray-700">
                    Aktif (tersedia untuk dipilih saat menambah peserta)
                </span>
            </label>
            <InputError className="mt-2" message={errors.is_active} />

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route("admin.universities.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? "Simpan Perubahan" : "Tambah Perguruan Tinggi"}
                </PrimaryButton>
            </div>
        </form>
    );
}
