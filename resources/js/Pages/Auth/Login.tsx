import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import MaterialIcon from "@/Components/MaterialIcon";
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

    const isAdminTab = tab === "admin";

    return (
        <GuestLayout>
            <Head title="Login" />

            <div className="mb-8">
                <h2 className="text-3xl font-bold text-gray-900">
                    {isAdminTab ? "Admin Portal" : "Sign In"}
                </h2>
                <p className="mt-2 text-sm text-gray-600">
                    {isAdminTab
                        ? "Manage interns and attendance records"
                        : "Access your attendance records"}
                </p>
            </div>

            {status && (
                <div className="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    <MaterialIcon name="check_circle" />
                    {status}
                </div>
            )}

            {/* Tab Toggle */}
            <div className="mb-8 flex gap-1 rounded-xl bg-gray-100 p-1">
                <button
                    type="button"
                    onClick={() => switchTab("intern")}
                    className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all ${
                        tab === "intern"
                            ? "bg-white text-gray-900 shadow-md"
                            : "text-gray-600 hover:text-gray-900"
                    }`}
                >
                    <MaterialIcon name="person" style={{ fontSize: 18 }} />
                    Peserta Magang
                </button>
                <button
                    type="button"
                    onClick={() => switchTab("admin")}
                    className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all ${
                        tab === "admin"
                            ? "bg-white text-gray-900 shadow-md"
                            : "text-gray-600 hover:text-gray-900"
                    }`}
                >
                    <MaterialIcon
                        name="shield_admin"
                        style={{ fontSize: 18 }}
                    />
                    Admin
                </button>
            </div>

            <form onSubmit={submit} className="space-y-4">
                {isAdminTab ? (
                    <div>
                        <InputLabel htmlFor="nik" value="NIK (Admin ID)" />
                        <div className="relative mt-2">
                            <MaterialIcon
                                name="badge"
                                style={{
                                    fontSize: 18,
                                    position: "absolute",
                                    left: "12px",
                                    top: "12px",
                                    color: "#9ca3af",
                                }}
                            />
                            <TextInput
                                id="nik"
                                type="text"
                                name="nik"
                                value={data.nik}
                                className="mt-0 block w-full border-gray-300 pl-10"
                                placeholder="Masukkan NIK Anda"
                                autoComplete="username"
                                isFocused={true}
                                onChange={(e) => setData("nik", e.target.value)}
                            />
                        </div>
                        <InputError message={errors.nik} className="mt-2" />
                    </div>
                ) : (
                    <>
                        <div>
                            <InputLabel
                                htmlFor="university_id"
                                value="Perguruan Tinggi"
                            />
                            <div className="relative mt-2">
                                <MaterialIcon
                                    name="school"
                                    style={{
                                        fontSize: 18,
                                        position: "absolute",
                                        left: "12px",
                                        top: "12px",
                                        color: "#9ca3af",
                                    }}
                                />
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

                        <div>
                            <InputLabel htmlFor="nim" value="NIM" />
                            <div className="relative mt-2">
                                <MaterialIcon
                                    name="badge"
                                    style={{
                                        fontSize: 18,
                                        position: "absolute",
                                        left: "12px",
                                        top: "12px",
                                        color: "#9ca3af",
                                    }}
                                />
                                <TextInput
                                    id="nim"
                                    type="text"
                                    name="nim"
                                    value={data.nim}
                                    className="mt-0 block w-full border-gray-300 pl-10"
                                    placeholder="Masukkan NIM Anda"
                                    autoComplete="username"
                                    onChange={(e) =>
                                        setData("nim", e.target.value)
                                    }
                                />
                            </div>
                            <InputError message={errors.nim} className="mt-2" />
                        </div>
                    </>
                )}

                <div>
                    <InputLabel htmlFor="password" value="Password" />
                    <div className="relative mt-2">
                        <MaterialIcon
                            name="lock"
                            style={{
                                fontSize: 18,
                                position: "absolute",
                                left: "12px",
                                top: "12px",
                                color: "#9ca3af",
                            }}
                        />
                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-0 block w-full border-gray-300 pl-10"
                            placeholder="Masukkan password Anda"
                            autoComplete="current-password"
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                        />
                    </div>
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="block">
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

                <button
                    type="submit"
                    disabled={processing}
                    className={`mt-6 w-full flex items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold text-white transition-all ${
                        processing
                            ? "bg-gray-400 cursor-not-allowed"
                            : isAdminTab
                              ? "bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 active:scale-95"
                              : "bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 active:scale-95"
                    }`}
                >
                    {processing ? (
                        <>
                            <div className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            Loading...
                        </>
                    ) : (
                        <>
                            <MaterialIcon
                                name="login"
                                style={{ fontSize: 18 }}
                            />
                            {isAdminTab ? "Login Admin" : "Login Peserta"}
                        </>
                    )}
                </button>
            </form>

            <div className="mt-6 border-t border-gray-200 pt-6">
                <p className="text-center text-xs text-gray-500">
                    Akses terbatas untuk pengguna yang berwenang. Semua
                    aktivitas dicatat untuk audit keamanan.
                </p>
            </div>
        </GuestLayout>
    );
}
