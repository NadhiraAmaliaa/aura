import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { StudyProgram, University } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

interface StudyProgramFormData {
    university_id: number | string;
    name: string;
    level: string;
    is_active: boolean;
    [key: string]: string | number | boolean;
}

export default function StudyProgramForm({
    studyProgram,
    university,
}: {
    studyProgram?: StudyProgram;
    university?: University | null;
}) {
    const isEdit = Boolean(studyProgram);

    const { data, setData, post, put, processing, errors } =
        useForm<StudyProgramFormData>({
            university_id: studyProgram?.university_id ?? university?.id ?? "",
            name: studyProgram?.name ?? "",
            level: studyProgram?.level ?? "",
            is_active: studyProgram ? (studyProgram.is_active ?? true) : true,
        });

    const universityDisplay =
        studyProgram?.university?.name ?? university?.name ?? "";

    const handleUniversity = (option: AutocompleteOption | null) => {
        setData("university_id", option ? option.id : "");
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && studyProgram) {
            put(route("admin.study-programs.update", studyProgram.id));
        } else {
            post(route("admin.study-programs.store"));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
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
                    onChange={(e) => setData("name", e.target.value)}
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
                    onChange={(e) => setData("level", e.target.value)}
                />
                <InputError className="mt-2" message={errors.level} />
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
                    href={route("admin.study-programs.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? "Simpan Perubahan" : "Tambah Program Studi"}
                </PrimaryButton>
            </div>
        </form>
    );
}
