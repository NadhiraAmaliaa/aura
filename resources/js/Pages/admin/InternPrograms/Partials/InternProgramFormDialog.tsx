import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextareaInput from "@/Components/TextareaInput";
import TextInput from "@/Components/TextInput";
import { InternProgram } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface ProgramFormData {
    name: string;
    description: string;
    [key: string]: string;
}

function initialData(program?: InternProgram): ProgramFormData {
    return {
        name: program?.name ?? "",
        description: program?.description ?? "",
    };
}

export default function InternProgramFormDialog({
    program,
    open,
    onOpenChange,
}: {
    program?: InternProgram;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEdit = Boolean(program);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<ProgramFormData>(initialData(program));

    useEffect(() => {
        if (open) {
            setData(initialData(program));
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

        if (isEdit && program) {
            put(route("admin.intern-programs.update", program.id), options);
        } else {
            post(route("admin.intern-programs.store"), options);
        }
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? "Ubah Program Magang" : "Tambah Program Magang"}
            description="Program magang dipakai untuk mengelompokkan jenis penempatan peserta."
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? "Simpan Perubahan" : "Tambah Program"}
        >
            <div>
                <InputLabel htmlFor="name" value="Nama Program" />
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
                <InputLabel htmlFor="description" value="Deskripsi (opsional)" />
                <TextareaInput
                    id="description"
                    className="mt-1 block w-full"
                    rows={4}
                    value={data.description}
                    onChange={(event) =>
                        setData("description", event.target.value)
                    }
                />
                <InputError className="mt-2" message={errors.description} />
            </div>
        </FormDialog>
    );
}
