import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division } from "@/types";
import { Head } from "@inertiajs/react";
import UserForm from "./UserForm";

export default function Create({ divisions }: { divisions: Division[] }) {
    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Tambah Akun
                </h1>
            }
        >
            <Head title="Tambah Akun" />

            <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm sm:p-8">
                <UserForm divisions={divisions} />
            </div>
        </AuthenticatedLayout>
    );
}
