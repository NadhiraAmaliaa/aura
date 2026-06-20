import Checkbox from "@/Components/Checkbox";
import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import { University } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface UniversityFormData {
    name: string;
    lldikti: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

function initialData(university?: University): UniversityFormData {
    return {
        name: university?.name ?? "",
        lldikti: university?.lldikti ?? "",
        is_active: university ? (university.is_active ?? true) : true,
    };
}

export default function UniversityFormDialog({
    university,
    open,
    onOpenChange,
}: {
    university?: University;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEdit = Boolean(university);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<UniversityFormData>(initialData(university));

    useEffect(() => {
        if (open) {
            setData(initialData(university));
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

        if (isEdit && university) {
            put(route("admin.universities.update", university.id), options);
        } else {
            post(route("admin.universities.store"), options);
        }
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? "Ubah Perguruan Tinggi" : "Tambah Perguruan Tinggi"}
            onSubmit={submit}
            processing={processing}
            submitLabel={
                isEdit ? "Simpan Perubahan" : "Tambah Perguruan Tinggi"
            }
        >
            <div>
                <InputLabel htmlFor="name" value="Nama Perguruan Tinggi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(event) => setData("name", event.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel htmlFor="lldikti" value="LLDikti (opsional)" />
                <TextInput
                    id="lldikti"
                    className="mt-1 block w-full"
                    value={data.lldikti}
                    onChange={(event) => setData("lldikti", event.target.value)}
                />
                <InputError className="mt-2" message={errors.lldikti} />
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_active}
                    onChange={(event) =>
                        setData("is_active", event.target.checked)
                    }
                />
                <span className="text-sm text-gray-700">
                    Aktif (tersedia untuk dipilih saat menambah peserta)
                </span>
            </label>
            <InputError className="mt-2" message={errors.is_active} />
        </FormDialog>
    );
}
