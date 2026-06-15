import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextareaInput from '@/Components/TextareaInput';
import {
    attendanceStatusLabels,
    formatDate,
    formatTime,
    workModeLabels,
} from '@/lib/labels';
import { Attendance } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Edit({ attendance }: { attendance: Attendance }) {
    const { data, setData, patch, processing, errors } = useForm({
        status: attendance.status,
        work_mode: attendance.work_mode ?? '',
        notes: attendance.notes ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('admin.attendances.update', attendance.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Koreksi Absensi
                </h2>
            }
        >
            <Head title="Koreksi Absensi" />

            <div className="rounded-lg bg-white p-6 shadow sm:p-8">
                <dl className="mb-6 grid grid-cols-2 gap-4 rounded-md bg-gray-50 p-4 text-sm">
                    <div>
                        <dt className="text-gray-500">Nama</dt>
                        <dd className="font-medium text-gray-900">
                            {attendance.user?.name}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Tanggal</dt>
                        <dd className="font-medium text-gray-900">
                            {formatDate(attendance.attendance_date)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Jam Masuk</dt>
                        <dd className="font-medium text-gray-900">
                            {formatTime(attendance.check_in_time)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Jam Pulang</dt>
                        <dd className="font-medium text-gray-900">
                            {formatTime(attendance.check_out_time)}
                        </dd>
                    </div>
                </dl>

                <form onSubmit={submit} className="space-y-6">
                    <div>
                        <InputLabel htmlFor="status" value="Status" />
                        <SelectInput
                            id="status"
                            className="mt-1 block w-full"
                            value={data.status}
                            onChange={(e) =>
                                setData(
                                    'status',
                                    e.target.value as Attendance['status'],
                                )
                            }
                        >
                            {Object.entries(attendanceStatusLabels).map(
                                ([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ),
                            )}
                        </SelectInput>
                        <InputError className="mt-2" message={errors.status} />
                    </div>

                    <div>
                        <InputLabel htmlFor="work_mode" value="Mode Kerja" />
                        <SelectInput
                            id="work_mode"
                            className="mt-1 block w-full"
                            value={data.work_mode}
                            onChange={(e) =>
                                setData('work_mode', e.target.value)
                            }
                        >
                            <option value="">Tidak ada</option>
                            {Object.entries(workModeLabels).map(
                                ([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ),
                            )}
                        </SelectInput>
                        <InputError
                            className="mt-2"
                            message={errors.work_mode}
                        />
                    </div>

                    <div>
                        <InputLabel htmlFor="notes" value="Catatan" />
                        <TextareaInput
                            id="notes"
                            rows={3}
                            className="mt-1 block w-full"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.notes} />
                    </div>

                    <div className="flex items-center justify-end gap-3">
                        <Link
                            href={route('admin.attendances.index')}
                            className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Batal
                        </Link>
                        <PrimaryButton disabled={processing}>
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
