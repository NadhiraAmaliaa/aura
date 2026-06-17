import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { Division } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

interface DivisionFormData {
    name: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

export default function DivisionForm({ division }: { division?: Division }) {
    const isEdit = Boolean(division);

    const { data, setData, post, put, processing, errors } =
        useForm<DivisionFormData>({
            name: division?.name ?? "",
            is_active: division ? division.is_active : true,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && division) {
            put(route("admin.divisions.update", division.id));
        } else {
            post(route("admin.divisions.store"));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="Nama Divisi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(e) => setData("name", e.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_active}
                    onChange={(e) => setData("is_active", e.target.checked)}
                />
                <span className="text-sm text-gray-700">
                    Divisi aktif (tersedia untuk dipilih saat menambah peserta)
                </span>
            </label>
            <InputError className="mt-2" message={errors.is_active} />

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route("admin.divisions.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? "Simpan Perubahan" : "Tambah Divisi"}
                </PrimaryButton>
            </div>
        </form>
    );
}
