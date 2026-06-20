import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Checkbox from "@/Components/Checkbox";
import FormDialog from "@/Components/admin/FormDialog";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import { StudyProgram, University } from "@/types";
import { useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect } from "react";

interface StudyProgramFormData {
    university_id: number | string;
    name: string;
    level: string;
    is_active: boolean;
    [key: string]: string | number | boolean;
}

function initialData(
    studyProgram?: StudyProgram,
    university?: University | null,
): StudyProgramFormData {
    return {
        university_id: studyProgram?.university_id ?? university?.id ?? "",
        name: studyProgram?.name ?? "",
        level: studyProgram?.level ?? "",
        is_active: studyProgram ? (studyProgram.is_active ?? true) : true,
    };
}

export default function StudyProgramFormDialog({
    studyProgram,
    university,
    open,
    onOpenChange,
}: {
    studyProgram?: StudyProgram;
    university?: University | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const isEdit = Boolean(studyProgram);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<StudyProgramFormData>(initialData(studyProgram, university));

    useEffect(() => {
        if (open) {
            setData(initialData(studyProgram, university));
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const universityDisplay =
        studyProgram?.university?.name ?? university?.name ?? "";

    const handleUniversity = (option: AutocompleteOption | null) => {
        setData("university_id", option ? option.id : "");
    };

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (isEdit && studyProgram) {
            put(
                route("admin.study-programs.update", studyProgram.id),
                options,
            );
        } else {
            post(route("admin.study-programs.store"), options);
        }
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={onOpenChange}
            title={isEdit ? "Ubah Program Studi" : "Tambah Program Studi"}
            onSubmit={submit}
            processing={processing}
            submitLabel={isEdit ? "Simpan Perubahan" : "Tambah Program Studi"}
        >
            <div>
                <InputLabel htmlFor="university_id" value="Perguruan Tinggi" />
                <div className="mt-1">
                    <Autocomplete
                        id="university_id"
                        url={route("lookup.universities")}
                        value={data.university_id}
                        displayValue={universityDisplay}
                        placeholder="Cari perguruan tinggi..."
                        onSelect={handleUniversity}
                    />
                </div>
                <InputError className="mt-2" message={errors.university_id} />
            </div>

            <div>
                <InputLabel htmlFor="name" value="Nama Program Studi" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(event) => setData("name", event.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel
                    htmlFor="level"
                    value="Jenjang (opsional, mis. D-III, S1)"
                />
                <TextInput
                    id="level"
                    className="mt-1 block w-full"
                    value={data.level}
                    onChange={(event) => setData("level", event.target.value)}
                />
                <InputError className="mt-2" message={errors.level} />
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
