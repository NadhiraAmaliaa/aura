import Autocomplete, { AutocompleteOption } from "@/Components/Autocomplete";
import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import MaterialIcon from "@/Components/MaterialIcon";
import TextInput from "@/Components/TextInput";
import { Head, useForm } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

type LoginTab = "admin" | "intern";

const PRIMARY_COLOR = "#1e3b8a";
const HERO_IMAGE =
    "https://images.unsplash.com/photo-1711192702535-eac61a78ecb0?q=80&w=1153&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D";

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
        <>
            <Head title="Login" />

            <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
                {/* Main Container */}
                <div
                    className="w-full max-w-6xl bg-white rounded-xl shadow-2xl flex overflow-hidden"
                    style={{ height: "600px" }}
                >
                    {/* LEFT: Hero Image Section */}
                    <section className="hidden md:flex relative w-[45%] overflow-visible">
                        {/* Teal Diagonal Slash Decoration - BEHIND image */}
                        <div
                            className="absolute pointer-events-none"
                            style={{
                                top: "20px",
                                bottom: "-20px",
                                right: "-30px",
                                width: "130px",
                                background: `linear-gradient(97.5deg, transparent 40%, ${PRIMARY_COLOR} 40%, ${PRIMARY_COLOR} 60%, transparent 60%)`,
                            }}
                        />

                        {/* Background Image with diagonal clip */}
                        <img
                            alt="Tea Plantation"
                            src={HERO_IMAGE}
                            className="absolute inset-0 w-full h-full object-cover relative"
                            style={{
                                clipPath:
                                    "polygon(0 0, 100% 0, 85% 100%, 0% 100%)",
                            }}
                        />

                        {/* Carousel Indicators */}
                        <div className="absolute top-8 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
                            <span className="w-8 h-1 bg-white rounded-full" />
                            <span className="w-2 h-1 bg-white/50 rounded-full" />
                            <span className="w-2 h-1 bg-white/50 rounded-full" />
                        </div>
                    </section>

                    {/* RIGHT: Login Form Section */}
                    <section className="w-full md:w-[55%] flex flex-col justify-center px-8 md:px-12 py-10 bg-white overflow-hidden">
                        {/* Logo and Heading */}
                        <div className="text-center mb-8">
                            <p className="text-sm text-gray-500 mb-2">
                                {isAdminTab
                                    ? "Selamat datang di"
                                    : "Masuk ke portal Anda"}
                            </p>
                            <h1
                                className="text-4xl font-bold"
                                style={{ color: PRIMARY_COLOR }}
                            >
                                {isAdminTab ? "Aura" : "Sistem Presensi"}
                            </h1>
                        </div>

                        {/* Status Message */}
                        {status && (
                            <div className="mb-6 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                                <MaterialIcon name="check_circle" />
                                {status}
                            </div>
                        )}

                        {/* Tab Toggle */}
                        <div
                            className="mb-8 flex gap-1 rounded-lg p-1"
                            style={{ backgroundColor: "#f0f0f0" }}
                        >
                            <button
                                type="button"
                                onClick={() => switchTab("intern")}
                                className={`flex flex-1 items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-semibold transition-all ${
                                    tab === "intern"
                                        ? "text-white shadow-md"
                                        : "text-gray-600 hover:text-gray-900"
                                }`}
                                style={{
                                    backgroundColor:
                                        tab === "intern"
                                            ? PRIMARY_COLOR
                                            : "transparent",
                                }}
                            >
                                <MaterialIcon
                                    name="person"
                                    style={{ fontSize: 18 }}
                                />
                                Peserta
                            </button>
                            <button
                                type="button"
                                onClick={() => switchTab("admin")}
                                className={`flex flex-1 items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-semibold transition-all ${
                                    tab === "admin"
                                        ? "text-white shadow-md"
                                        : "text-gray-600 hover:text-gray-900"
                                }`}
                                style={{
                                    backgroundColor:
                                        tab === "admin"
                                            ? PRIMARY_COLOR
                                            : "transparent",
                                }}
                            >
                                {/* <MaterialIcon
                                    name="shield_admin"
                                    style={{ fontSize: 18 }}
                                /> */}
                                Admin
                            </button>
                        </div>

                        {/* Form */}
                        <form onSubmit={submit} className="space-y-5">
                            {isAdminTab ? (
                                <div>
                                    <InputLabel
                                        htmlFor="nik"
                                        value="Username"
                                    />
                                    <div className="relative mt-2">
                                        <MaterialIcon
                                            name="badge"
                                            style={{
                                                fontSize: 18,
                                                position: "absolute",
                                                left: "12px",
                                                top: "12px",
                                                color: "#d1d5db",
                                            }}
                                        />
                                        <TextInput
                                            id="nik"
                                            type="text"
                                            name="nik"
                                            value={data.nik}
                                            className="mt-0 block w-full border-gray-300 pl-10 rounded-md"
                                            placeholder="Masukkan NIK Anda"
                                            autoComplete="username"
                                            isFocused={true}
                                            onChange={(e) =>
                                                setData("nik", e.target.value)
                                            }
                                            style={{
                                                borderColor: "#d1d5db",
                                            }}
                                        />
                                    </div>
                                    <InputError
                                        message={errors.nik}
                                        className="mt-2"
                                    />
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
                                                    color: "#d1d5db",
                                                }}
                                            />
                                            <Autocomplete
                                                id="university_id"
                                                url={route(
                                                    "lookup.universities",
                                                )}
                                                value={data.university_id}
                                                displayValue={
                                                    data.university_name
                                                }
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
                                                    color: "#d1d5db",
                                                }}
                                            />
                                            <TextInput
                                                id="nim"
                                                type="text"
                                                name="nim"
                                                value={data.nim}
                                                className="mt-0 block w-full border-gray-300 pl-10 rounded-md"
                                                placeholder="Masukkan NIM Anda"
                                                autoComplete="username"
                                                onChange={(e) =>
                                                    setData(
                                                        "nim",
                                                        e.target.value,
                                                    )
                                                }
                                                style={{
                                                    borderColor: "#d1d5db",
                                                }}
                                            />
                                        </div>
                                        <InputError
                                            message={errors.nim}
                                            className="mt-2"
                                        />
                                    </div>
                                </>
                            )}

                            <div>
                                <InputLabel
                                    htmlFor="password"
                                    value="Password"
                                />
                                <div className="relative mt-2">
                                    <MaterialIcon
                                        name="lock"
                                        style={{
                                            fontSize: 18,
                                            position: "absolute",
                                            left: "12px",
                                            top: "12px",
                                            color: "#d1d5db",
                                        }}
                                    />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        name="password"
                                        value={data.password}
                                        className="mt-0 block w-full border-gray-300 pl-10 rounded-md"
                                        placeholder="Masukkan password Anda"
                                        autoComplete="current-password"
                                        onChange={(e) =>
                                            setData("password", e.target.value)
                                        }
                                        style={{
                                            borderColor: "#d1d5db",
                                        }}
                                    />
                                </div>
                                <InputError
                                    message={errors.password}
                                    className="mt-2"
                                />
                            </div>

                            <div className="flex justify-between items-center">
                                <label className="flex items-center">
                                    <Checkbox
                                        name="remember"
                                        checked={data.remember}
                                        onChange={(e) =>
                                            setData(
                                                "remember",
                                                e.target.checked,
                                            )
                                        }
                                    />
                                    <span className="ms-2 text-sm text-gray-600">
                                        Ingat saya
                                    </span>
                                </label>
                                {isAdminTab && (
                                    <a
                                        href="#"
                                        className="text-xs font-medium hover:underline"
                                        style={{ color: PRIMARY_COLOR }}
                                    >
                                        Lupa password?
                                    </a>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full flex items-center justify-center gap-2 rounded-md py-3 text-sm font-bold text-white transition-all uppercase tracking-wider"
                                style={{
                                    backgroundColor: processing
                                        ? "#9ca3af"
                                        : PRIMARY_COLOR,
                                    cursor: processing
                                        ? "not-allowed"
                                        : "pointer",
                                    opacity: processing ? 0.7 : 1,
                                }}
                                onMouseEnter={(e) => {
                                    if (!processing) {
                                        e.currentTarget.style.backgroundColor =
                                            "#172554";
                                    }
                                }}
                                onMouseLeave={(e) => {
                                    if (!processing) {
                                        e.currentTarget.style.backgroundColor =
                                            PRIMARY_COLOR;
                                    }
                                }}
                            >
                                {processing ? (
                                    <>
                                        <div className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                        Memproses...
                                    </>
                                ) : (
                                    <>
                                        <MaterialIcon
                                            name="login"
                                            style={{ fontSize: 18 }}
                                        />
                                        {isAdminTab
                                            ? "Login Admin"
                                            : "Login Peserta"}
                                    </>
                                )}
                            </button>
                        </form>

                        {/* Sign Up Footer */}
                        <div className="text-center text-sm text-gray-600">
                            Belum punya akun?{" "}
                            <a
                                href="#"
                                className="font-bold hover:underline"
                                style={{ color: PRIMARY_COLOR }}
                            >
                                Hubungi Admin
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
