import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import { postJson } from "@/lib/http";
import { Intern, InternProgram } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

interface InternFormData {
    name: string;
    password: string;
    password_confirmation: string;
    intern_program_id: string;
    university_id: number | string;
    study_program_id: number | string;
    division_id: number | string;
    nim: string;
    phone: string;
    start_date: string;
    end_date: string;
    is_active: boolean;
    [key: string]: string | number | boolean;
}

export default function InternForm({
    programs,
    intern,
}: {
    programs: InternProgram[];
    intern?: Intern;
}) {
    const isEdit = Boolean(intern);

    const { data, setData, post, put, processing, errors } =
        useForm<InternFormData>({
            name: intern?.user?.name ?? "",
            password: "",
            password_confirmation: "",
            intern_program_id: intern?.intern_program_id
                ? String(intern.intern_program_id)
                : "",
            university_id: intern?.university_id ?? "",
            study_program_id: intern?.study_program_id ?? "",
            division_id: intern?.division_id ?? "",
            nim: intern?.nim ?? "",
            phone: intern?.phone ?? "",
            start_date: intern?.start_date ?? "",
            end_date: intern?.end_date ?? "",
            is_active: intern ? intern.status !== "inactive" : true,
        });

    // Display names for the autocomplete fields when editing. The relation
    // names are preferred, falling back to the denormalised text columns.
    const universityDisplay =
        intern?.university_ref?.name ?? intern?.university ?? "";
    const studyProgramDisplay =
        intern?.study_program?.name ?? intern?.major ?? "";
    const divisionDisplay =
        intern?.division_ref?.name ?? intern?.division ?? "";

    const handleUniversity = (option: AutocompleteOption | null) => {
        setData((previous) => ({
            ...previous,
            university_id: option ? option.id : "",
            // Changing the university invalidates the chosen study program.
            study_program_id: "",
        }));
    };

    const handleStudyProgram = (option: AutocompleteOption | null) => {
        setData("study_program_id", option ? option.id : "");
    };

    const handleDivision = (option: AutocompleteOption | null) => {
        setData("division_id", option ? option.id : "");
    };

    // Quick-create a university from the autocomplete when it is missing. The
    // created option is returned so the Autocomplete selects it immediately
    // (which also resets the study program via handleUniversity).
    const createUniversity = async (
        name: string,
    ): Promise<AutocompleteOption | null> => {
        return postJson(route("admin.lookup.universities.store"), { name });
    };

    // Quick-create a study program under the currently selected university. A
    // study program must always belong to a university.
    const createStudyProgram = async (
        name: string,
    ): Promise<AutocompleteOption | null> => {
        if (!data.university_id) {
            return null;
        }

        return postJson(route("admin.lookup.study-programs.store"), {
            name,
            university_id: data.university_id,
        });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && intern) {
            put(route("admin.interns.update", intern.id));
        } else {
            post(route("admin.interns.store"));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-8">
            <section>
                <h3 className="mb-4 text-base font-semibold text-gray-800">
                    Akun Pengguna
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="name" value="Nama Lengkap" />
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
                            htmlFor="password"
                            value={
                                isEdit
                                    ? "Kata Sandi Baru (opsional)"
                                    : "Kata Sandi"
                            }
                        />
                        <TextInput
                            id="password"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.password}
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.password}
                        />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="password_confirmation"
                            value="Konfirmasi Kata Sandi"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.password_confirmation}
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData("password_confirmation", e.target.value)
                            }
                        />
                    </div>
                </div>
            </section>

            <section>
                <h3 className="mb-4 text-base font-semibold text-gray-800">
                    Data Magang
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel
                            htmlFor="intern_program_id"
                            value="Program Magang"
                        />
                        <SelectInput
                            id="intern_program_id"
                            className="mt-1 block w-full"
                            value={data.intern_program_id}
                            onChange={(e) =>
                                setData("intern_program_id", e.target.value)
                            }
                        >
                            <option value="">Pilih program</option>
                            {programs.map((program) => (
                                <option key={program.id} value={program.id}>
                                    {program.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError
                            className="mt-2"
                            message={errors.intern_program_id}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="nim" value="NIM" />
                        <TextInput
                            id="nim"
                            className="mt-1 block w-full"
                            value={data.nim}
                            onChange={(e) => setData("nim", e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.nim} />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="university_id"
                            value="Perguruan Tinggi"
                        />
                        <div className="mt-1">
                            <Autocomplete
                                id="university_id"
                                url={route("lookup.universities")}
                                value={data.university_id}
                                displayValue={universityDisplay}
                                placeholder="Cari perguruan tinggi..."
                                onSelect={handleUniversity}
                                onCreate={createUniversity}
                                createLabel="Tambah perguruan tinggi"
                            />
                        </div>
                        <InputError
                            className="mt-2"
                            message={errors.university_id}
                        />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="study_program_id"
                            value="Program Studi"
                        />
                        <div className="mt-1">
                            <Autocomplete
                                id="study_program_id"
                                url={route("admin.lookup.study-programs")}
                                value={data.study_program_id}
                                displayValue={studyProgramDisplay}
                                params={{ university_id: data.university_id }}
                                disabled={!data.university_id}
                                placeholder={
                                    data.university_id
                                        ? "Cari program studi..."
                                        : "Pilih perguruan tinggi dahulu"
                                }
                                onSelect={handleStudyProgram}
                                onCreate={createStudyProgram}
                                createLabel="Tambah program studi"
                            />
                        </div>
                        <InputError
                            className="mt-2"
                            message={errors.study_program_id}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="division_id" value="Divisi" />
                        <div className="mt-1">
                            <Autocomplete
                                id="division_id"
                                url={route("admin.lookup.divisions")}
                                value={data.division_id}
                                displayValue={divisionDisplay}
                                placeholder="Cari divisi..."
                                onSelect={handleDivision}
                            />
                        </div>
                        <InputError
                            className="mt-2"
                            message={errors.division_id}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="phone" value="Nomor Telepon" />
                        <TextInput
                            id="phone"
                            className="mt-1 block w-full"
                            value={data.phone}
                            onChange={(e) => setData("phone", e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.phone} />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="start_date"
                            value="Tanggal Mulai"
                        />
                        <TextInput
                            id="start_date"
                            type="date"
                            className="mt-1 block w-full"
                            value={data.start_date}
                            onChange={(e) =>
                                setData("start_date", e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.start_date}
                        />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="end_date"
                            value="Tanggal Selesai"
                        />
                        <TextInput
                            id="end_date"
                            type="date"
                            className="mt-1 block w-full"
                            value={data.end_date}
                            onChange={(e) =>
                                setData("end_date", e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.end_date}
                        />
                    </div>
                </div>

                <div className="mt-4 rounded-md border border-gray-200 bg-gray-50 p-4">
                    <label className="flex items-start gap-3">
                        <Checkbox
                            checked={data.is_active}
                            onChange={(e) =>
                                setData("is_active", e.target.checked)
                            }
                        />
                        <span className="text-sm">
                            <span className="font-medium text-gray-800">
                                Akun aktif
                            </span>
                            <span className="mt-0.5 block text-gray-500">
                                Status magang (akan datang, aktif, selesai)
                                dihitung otomatis dari tanggal mulai dan
                                selesai. Nonaktifkan untuk mencabut akses
                                peserta secara manual.
                            </span>
                        </span>
                    </label>
                    <InputError className="mt-2" message={errors.is_active} />
                </div>
            </section>

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route("admin.interns.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? "Simpan Perubahan" : "Tambah Peserta"}
                </PrimaryButton>
            </div>
        </form>
    );
}
