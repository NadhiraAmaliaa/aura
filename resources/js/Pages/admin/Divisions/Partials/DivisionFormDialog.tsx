import Checkbox from "@/Components/Checkbox";
import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import { Division } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface DivisionFormData {
    name: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

function initialData(division?: Division): DivisionFormData {
    return {
        name: division?.name ?? "",
        is_active: division ? division.is_active : true,
    };
}

export default function DivisionFormDialog({
    division,
    open,
    onOpenChange,
}: {
    division?: Division;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEdit = Boolean(division);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<DivisionFormData>(initialData(division));

    useEffect(() => {
        if (open) {
            setData(initialData(division));
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

        if (isEdit && division) {
            put(route("admin.divisions.update", division.id), options);
        } else {
            post(route("admin.divisions.store"), options);
        }
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? "Ubah Divisi" : "Tambah Divisi"}
            description="Divisi digunakan untuk mengelompokkan penempatan peserta magang."
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? "Simpan Perubahan" : "Tambah Divisi"}
        >
            <div>
                <InputLabel htmlFor="name" value="Nama Divisi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(event) => setData("name", event.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <label className="flex items-center gap-3">
                <Checkbox
                    checked={data.is_active}
                    onChange={(event) =>
                        setData("is_active", event.target.checked)
                    }
                />
                <span className="text-sm text-gray-700">
                    Divisi aktif (tersedia untuk dipilih saat menambah peserta)
                </span>
            </label>
            <InputError message={errors.is_active} />
        </FormDialog>
    );
}
