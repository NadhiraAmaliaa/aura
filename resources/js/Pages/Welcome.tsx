import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

export default function Welcome() {
    const user = usePage<PageProps>().props.auth.user;

    return (
        <>
            <Head title="Selamat Datang" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-green-50 to-green-100 px-6">
                <div className="w-full max-w-2xl rounded-2xl bg-white p-10 text-center shadow-lg">
                    <div className="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-xl bg-green-700 text-2xl font-bold text-white">
                        P
                    </div>
                    <h1 className="text-3xl font-bold text-gray-900">
                        Aplikasi Absensi Magang PTPN
                    </h1>
                    <p className="mt-3 text-gray-600">
                        Kelola kehadiran, pengajuan izin, dan rekap absensi
                        peserta magang dalam satu platform.
                    </p>

                    <div className="mt-8 flex justify-center gap-4">
                        {user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-md bg-green-700 px-6 py-3 font-semibold text-white transition hover:bg-green-800"
                            >
                                Buka Dashboard
                            </Link>
                        ) : (
                            <Link
                                href={route('login')}
                                className="rounded-md bg-green-700 px-6 py-3 font-semibold text-white transition hover:bg-green-800"
                            >
                                Masuk
                            </Link>
                        )}
                    </div>
                </div>

                <p className="mt-6 text-sm text-gray-500">
                    &copy; {new Date().getFullYear()} PTPN. Hak cipta dilindungi.
                </p>
            </div>
        </>
    );
}
