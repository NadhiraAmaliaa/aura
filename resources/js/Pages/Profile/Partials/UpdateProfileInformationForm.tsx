import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { useForm, usePage } from "@inertiajs/react";
import { FormEventHandler } from "react";
import { PageProps } from "@/types";

export default function UpdateProfileInformation({
    className = "",
}: {
    status?: string;
    className?: string;
}) {
    const user = usePage<PageProps>().props.auth.user!;
    const isAdmin = user.role === "admin";
    const isIntern = user.role === "intern";

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            nik: user.nik ?? "",
            email: user.email ?? "",
            phone: user.intern?.phone ?? "",
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route("profile.update"));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">
                    Informasi Profil
                </h2>
                <p className="mt-1 text-sm text-gray-600">
                    Perbarui informasi profil akun Anda.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Nama" />
                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />
                    <InputError className="mt-2" message={errors.name} />
                </div>

                {isAdmin && (
                    <div>
                        <InputLabel htmlFor="nik" value="NIK" />
                        <TextInput
                            id="nik"
                            type="text"
                            className="mt-1 block w-full"
                            value={data.nik}
                            onChange={(e) => setData("nik", e.target.value)}
                            required
                            autoComplete="username"
                        />
                        <InputError className="mt-2" message={errors.nik} />
                    </div>
                )}

                {isIntern && (
                    <>
                        <div>
                            <InputLabel htmlFor="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) =>
                                    setData("email", e.target.value)
                                }
                                autoComplete="email"
                            />
                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="phone"
                                value="Nomor Telepon"
                            />
                            <TextInput
                                id="phone"
                                type="tel"
                                className="mt-1 block w-full"
                                value={data.phone}
                                onChange={(e) =>
                                    setData("phone", e.target.value)
                                }
                                autoComplete="tel"
                            />
                            <InputError className="mt-2" message={errors.phone} />
                        </div>
                    </>
                )}

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Simpan</PrimaryButton>

                    {recentlySuccessful && (
                        <p className="text-sm text-gray-600">Tersimpan.</p>
                    )}
                </div>
            </form>
        </section>
    );
}
