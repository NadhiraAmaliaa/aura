import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Division, ManagedUser } from "@/types";
import { Head } from "@inertiajs/react";
import UserForm from "./UserForm";

export default function Edit({
    user,
    divisions,
}: {
    user: ManagedUser;
    divisions: Division[];
}) {
    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-[28px] font-extrabold tracking-tight text-on-surface">
                    Ubah Akun
                </h1>
            }
        >
            <Head title="Ubah Akun" />

            <div className="rounded-xl border border-outline-variant bg-white p-6 shadow-sm sm:p-8">
                <UserForm divisions={divisions} user={user} />
            </div>
        </AuthenticatedLayout>
    );
}
