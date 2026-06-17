import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, useForm } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

type LoginTab = "admin" | "intern";

export default function Login({ status }: { status?: string }) {
    const [tab, setTab] = useState<LoginTab>("intern");

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            login_as: "intern",
            // Admin field
            nik: "",
            // Intern fields
            university_id: "" as number | string,
            university_name: "",
            nim: "",
            // Shared
            password: "",
            remember: false,
        });

    const switchTab = (next: LoginTab) => {
        setTab(next);
        setData("login_as", next);
        clearErrors();
        reset("password");
    };

    const handleUniversity = (option: AutocompleteOption | null) => {
        setData((previous) => ({
            ...previous,
            university_id: option ? option.id : "",
            university_name: option ? option.name : "",
        }));
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    const tabClass = (value: LoginTab) =>
        "flex-1 rounded-md px-4 py-2 text-sm font-medium transition " +
        (tab === value
            ? "bg-green-600 text-white shadow"
            : "text-gray-600 hover:bg-gray-100");

    return (
        <GuestLayout>
            <Head title="Masuk" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="mb-6 flex gap-2 rounded-lg bg-gray-100 p-1">
                <button
                    type="button"
                    className={tabClass("intern")}
                    onClick={() => switchTab("intern")}
                >
                    Peserta Magang
                </button>
                <button
                    type="button"
                    className={tabClass("admin")}
                    onClick={() => switchTab("admin")}
                >
                    Admin
                </button>
            </div>

            <form onSubmit={submit}>
                {tab === "intern" ? (
                    <>
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
                                    displayValue={data.university_name}
                                    placeholder="Cari perguruan tinggi..."
                                    onSelect={handleUniversity}
                                />
                            </div>
                            <InputError
                                message={errors.university_id}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-4">
                            <InputLabel htmlFor="nim" value="NIM" />
                            <TextInput
                                id="nim"
                                type="text"
                                name="nim"
                                value={data.nim}
                                className="mt-1 block w-full"
                                autoComplete="username"
                                onChange={(e) => setData("nim", e.target.value)}
                            />
                            <InputError message={errors.nim} className="mt-2" />
                        </div>
                    </>
                ) : (
                    <div>
                        <InputLabel htmlFor="nik" value="NIK" />
                        <TextInput
                            id="nik"
                            type="text"
                            name="nik"
                            value={data.nik}
                            className="mt-1 block w-full"
                            autoComplete="username"
                            isFocused={true}
                            onChange={(e) => setData("nik", e.target.value)}
                        />
                        <InputError message={errors.nik} className="mt-2" />
                    </div>
                )}

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Kata Sandi" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData("password", e.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData("remember", e.target.checked)
                            }
                        />
                        <span className="ms-2 text-sm text-gray-600">
                            Ingat saya
                        </span>
                    </label>
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Masuk
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
