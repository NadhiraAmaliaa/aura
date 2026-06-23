import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import MaterialIcon from "@/Components/MaterialIcon";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import { Division, ManagedUser } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import { FormEventHandler, useState } from "react";

interface UserFormData {
    name: string;
    email: string;
    nik: string;
    password: string;
    password_confirmation: string;
    role: "admin" | "supervisor";
    division_id: string;
    is_active: boolean;
    [key: string]: string | boolean;
}

export default function UserForm({
    user,
    divisions,
}: {
    user?: ManagedUser;
    divisions: Division[];
}) {
    const isEdit = Boolean(user);
    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] =
        useState(false);

    const { data, setData, post, put, processing, errors } =
        useForm<UserFormData>({
            name: user?.name ?? "",
            email: user?.email ?? "",
            nik: user?.nik ?? "",
            password: "",
            password_confirmation: "",
            role: user?.role ?? "admin",
            division_id: user?.division_id ? String(user.division_id) : "",
            is_active: user ? user.is_active : true,
        });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (isEdit && user) {
            put(route("admin.users.update", user.id));
        } else {
            post(route("admin.users.store"));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-8">
            <section>
                <h3 className="mb-4 text-base font-semibold text-gray-800">
                    Data Akun
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <InputLabel htmlFor="name" value="Nama" />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            isFocused
                            onChange={(event) =>
                                setData("name", event.target.value)
                            }
                        />
                        <InputError className="mt-2" message={errors.name} />
                    </div>
                    <div>
                        <InputLabel htmlFor="nik" value="NIK" />
                        <TextInput
                            id="nik"
                            className="mt-1 block w-full"
                            value={data.nik}
                            onChange={(event) =>
                                setData("nik", event.target.value)
                            }
                        />
                        <InputError className="mt-2" message={errors.nik} />
                    </div>
                    <div>
                        <InputLabel htmlFor="username" value="Username" />
                        <TextInput
                            id="username"
                            className="mt-1 block w-full cursor-not-allowed bg-surface-container-low text-on-surface-variant"
                            value={data.nik}
                            readOnly
                            tabIndex={-1}
                            placeholder="Mengikuti NIK"
                        />
                        <p className="mt-1 text-xs text-on-surface-variant">
                            Username otomatis mengikuti NIK.
                        </p>
                    </div>
                    <div className="sm:col-span-2">
                        <InputLabel
                            htmlFor="email"
                            value={
                                data.role === "supervisor"
                                    ? "Email (wajib untuk Supervisor)"
                                    : "Email"
                            }
                        />
                        <TextInput
                            id="email"
                            type="email"
                            className="mt-1 block w-full"
                            value={data.email}
                            placeholder="contoh@email.com"
                            onChange={(event) =>
                                setData("email", event.target.value)
                            }
                        />
                        <InputError className="mt-2" message={errors.email} />
                    </div>
                    <div className="sm:col-span-2">
                        <InputLabel
                            htmlFor="password"
                            value={
                                isEdit
                                    ? "Kata Sandi Baru (opsional)"
                                    : "Kata Sandi"
                            }
                        />
                        <div className="relative mt-1">
                            <TextInput
                                id="password"
                                type={showPassword ? "text" : "password"}
                                className="block w-full pr-10"
                                value={data.password}
                                autoComplete="new-password"
                                placeholder={
                                    isEdit
                                        ? "Kosongkan jika tidak diubah"
                                        : "Minimal 3 karakter"
                                }
                                onChange={(event) =>
                                    setData("password", event.target.value)
                                }
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword((prev) => !prev)}
                                aria-label={
                                    showPassword
                                        ? "Sembunyikan kata sandi"
                                        : "Tampilkan kata sandi"
                                }
                                className="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant transition-colors hover:text-primary"
                            >
                                <MaterialIcon
                                    name={
                                        showPassword
                                            ? "visibility_off"
                                            : "visibility"
                                    }
                                    style={{ fontSize: 20 }}
                                />
                            </button>
                        </div>
                        <InputError
                            className="mt-2"
                            message={errors.password}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <InputLabel
                            htmlFor="password_confirmation"
                            value="Konfirmasi Kata Sandi"
                        />
                        <div className="relative mt-1">
                            <TextInput
                                id="password_confirmation"
                                type={
                                    showPasswordConfirmation
                                        ? "text"
                                        : "password"
                                }
                                className="block w-full pr-10"
                                value={data.password_confirmation}
                                autoComplete="new-password"
                                placeholder={
                                    isEdit
                                        ? "Kosongkan jika tidak diubah"
                                        : "Ulangi kata sandi"
                                }
                                onChange={(event) =>
                                    setData(
                                        "password_confirmation",
                                        event.target.value,
                                    )
                                }
                            />
                            <button
                                type="button"
                                onClick={() =>
                                    setShowPasswordConfirmation((prev) => !prev)
                                }
                                aria-label={
                                    showPasswordConfirmation
                                        ? "Sembunyikan kata sandi"
                                        : "Tampilkan kata sandi"
                                }
                                className="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant transition-colors hover:text-primary"
                            >
                                <MaterialIcon
                                    name={
                                        showPasswordConfirmation
                                            ? "visibility_off"
                                            : "visibility"
                                    }
                                    style={{ fontSize: 20 }}
                                />
                            </button>
                        </div>
                        <InputError
                            className="mt-2"
                            message={errors.password_confirmation}
                        />
                    </div>
                </div>
            </section>

            <section>
                <h3 className="mb-4 text-base font-semibold text-gray-800">
                    Peran & Status
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="role" value="Peran" />
                        <SelectInput
                            id="role"
                            className="mt-1 block w-full"
                            value={data.role}
                            onChange={(event) => {
                                const role = event.target.value as
                                    | "admin"
                                    | "supervisor";
                                setData((previous) => ({
                                    ...previous,
                                    role,
                                    division_id:
                                        role === "admin"
                                            ? ""
                                            : previous.division_id,
                                }));
                            }}
                        >
                            <option value="admin">Admin</option>
                            <option value="supervisor">Supervisor</option>
                        </SelectInput>
                        <InputError className="mt-2" message={errors.role} />
                    </div>
                    <div>
                        <InputLabel htmlFor="division_id" value="Divisi" />
                        <SelectInput
                            id="division_id"
                            className="mt-1 block w-full disabled:cursor-not-allowed disabled:bg-surface-container-low disabled:text-on-surface-variant"
                            value={data.division_id}
                            disabled={data.role === "admin"}
                            onChange={(event) =>
                                setData("division_id", event.target.value)
                            }
                        >
                            <option value="">
                                {data.role === "admin"
                                    ? "Tidak diperlukan"
                                    : "Pilih divisi"}
                            </option>
                            {divisions.map((division) => (
                                <option key={division.id} value={division.id}>
                                    {division.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError
                            className="mt-2"
                            message={errors.division_id}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="status" value="Status" />
                        <SelectInput
                            id="status"
                            className="mt-1 block w-full"
                            value={data.is_active ? "1" : "0"}
                            onChange={(event) =>
                                setData("is_active", event.target.value === "1")
                            }
                        >
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </SelectInput>
                        <InputError
                            className="mt-2"
                            message={errors.is_active}
                        />
                    </div>
                </div>
            </section>

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route("admin.users.index")}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? "Simpan Perubahan" : "Tambah Akun"}
                </PrimaryButton>
            </div>
        </form>
    );
}
