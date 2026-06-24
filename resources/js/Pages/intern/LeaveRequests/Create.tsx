import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import TextareaInput from "@/Components/TextareaInput";
import { leaveTypeLabels } from "@/lib/labels";
import { LeaveType } from "@/types";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

const toDateInput = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        type: "izin" as LeaveType,
        reason: "",
        start_date: "",
        end_date: "",
        contact_phone: "",
        address: "",
        evidence: null as File | null,
    });

    const isSakit = data.type === "sakit";

    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);
    const minStartDate = isSakit ? toDateInput(today) : toDateInput(tomorrow);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route("intern.leave-requests.store"), {
            forceFormData: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Ajukan Izin
                </h2>
            }
        >
            <Head title="Ajukan Izin" />

            <div className="max-w-2xl rounded-lg bg-white p-6 shadow">
                <form onSubmit={submit} className="space-y-6">
                    <div>
                        <InputLabel htmlFor="type" value="Jenis Pengajuan" />
                        <SelectInput
                            id="type"
                            className="mt-1 block w-full"
                            value={data.type}
                            onChange={(e) =>
                                setData("type", e.target.value as LeaveType)
                            }
                        >
                            {Object.entries(leaveTypeLabels).map(
                                ([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ),
                            )}
                        </SelectInput>
                        <InputError className="mt-2" message={errors.type} />
                        <p className="mt-2 text-sm text-gray-500">
                            {isSakit
                                ? "Pengajuan sakit dapat dibuat untuk hari ini. Lampiran bukti (PDF/gambar) wajib diunggah."
                                : "Pengajuan izin minimal H-1. Lampiran bukti bersifat opsional."}
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel
                                htmlFor="start_date"
                                value="Tanggal Mulai"
                            />
                            <TextInput
                                id="start_date"
                                type="date"
                                min={minStartDate}
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

                    <div>
                        <InputLabel htmlFor="reason" value="Alasan" />
                        <TextareaInput
                            id="reason"
                            className="mt-1 block w-full"
                            rows={4}
                            value={data.reason}
                            onChange={(e) => setData("reason", e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.reason} />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="contact_phone"
                            value="Nomor Kontak (opsional)"
                        />
                        <TextInput
                            id="contact_phone"
                            type="text"
                            className="mt-1 block w-full"
                            value={data.contact_phone}
                            onChange={(e) =>
                                setData("contact_phone", e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.contact_phone}
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="address"
                            value="Alamat Selama Izin (opsional)"
                        />
                        <TextareaInput
                            id="address"
                            className="mt-1 block w-full"
                            rows={2}
                            value={data.address}
                            onChange={(e) => setData("address", e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.address} />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="evidence"
                            value={
                                isSakit
                                    ? "Lampiran Bukti (wajib)"
                                    : "Lampiran Bukti (opsional)"
                            }
                        />
                        <input
                            id="evidence"
                            type="file"
                            accept=".pdf,image/*"
                            className="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                            onChange={(e) =>
                                setData("evidence", e.target.files?.[0] ?? null)
                            }
                        />
                        <p className="mt-1 text-sm text-gray-500">
                            Format PDF atau gambar (jpg, jpeg, png, webp),
                            maksimal 5 MB.
                        </p>
                        <InputError
                            className="mt-2"
                            message={errors.evidence}
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        <PrimaryButton disabled={processing}>
                            Kirim Pengajuan
                        </PrimaryButton>
                        <Link href={route("intern.leave-requests.index")}>
                            <SecondaryButton type="button">
                                Batal
                            </SecondaryButton>
                        </Link>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
