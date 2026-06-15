import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import { internStatusLabels } from '@/lib/labels';
import { Intern, InternProgram } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface InternFormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    intern_program_id: string;
    nim: string;
    phone: string;
    university: string;
    major: string;
    division: string;
    start_date: string;
    end_date: string;
    status: string;
    [key: string]: string;
}

export default function InternForm({
    programs,
    intern,
}: {
    programs: InternProgram[];
    intern?: Intern;
}) {
    const isEdit = Boolean(intern);

    const { data, setData, post, put, processing, errors } =
        useForm<InternFormData>({
            name: intern?.user?.name ?? '',
            email: intern?.user?.email ?? '',
            password: '',
            password_confirmation: '',
            intern_program_id: intern?.intern_program_id
                ? String(intern.intern_program_id)
                : '',
            nim: intern?.nim ?? '',
            phone: intern?.phone ?? '',
            university: intern?.university ?? '',
            major: intern?.major ?? '',
            division: intern?.division ?? '',
            start_date: intern?.start_date ?? '',
            end_date: intern?.end_date ?? '',
            status: intern?.status ?? 'active',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && intern) {
            put(route('admin.interns.update', intern.id));
        } else {
            post(route('admin.interns.store'));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-8">
            <section>
                <h3 className="mb-4 text-base font-semibold text-gray-800">
                    Akun Pengguna
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="name" value="Nama Lengkap" />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.name} />
                    </div>
                    <div>
                        <InputLabel htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            className="mt-1 block w-full"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.email} />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="password"
                            value={
                                isEdit
                                    ? 'Kata Sandi Baru (opsional)'
                                    : 'Kata Sandi'
                            }
                        />
                        <TextInput
                            id="password"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.password}
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                        />
                        <InputError className="mt-2" message={errors.password} />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="password_confirmation"
                            value="Konfirmasi Kata Sandi"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            className="mt-1 block w-full"
                            value={data.password_confirmation}
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData(
                                    'password_confirmation',
                                    e.target.value,
                                )
                            }
                        />
                    </div>
                </div>
            </section>

            <section>
                <h3 className="mb-4 text-base font-semibold text-gray-800">
                    Data Magang
                </h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel
                            htmlFor="intern_program_id"
                            value="Program Magang"
                        />
                        <SelectInput
                            id="intern_program_id"
                            className="mt-1 block w-full"
                            value={data.intern_program_id}
                            onChange={(e) =>
                                setData('intern_program_id', e.target.value)
                            }
                        >
                            <option value="">Pilih program</option>
                            {programs.map((program) => (
                                <option key={program.id} value={program.id}>
                                    {program.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError
                            className="mt-2"
                            message={errors.intern_program_id}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="nim" value="NIM" />
                        <TextInput
                            id="nim"
                            className="mt-1 block w-full"
                            value={data.nim}
                            onChange={(e) => setData('nim', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.nim} />
                    </div>
                    <div>
                        <InputLabel htmlFor="phone" value="Nomor Telepon" />
                        <TextInput
                            id="phone"
                            className="mt-1 block w-full"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.phone} />
                    </div>
                    <div>
                        <InputLabel htmlFor="university" value="Universitas" />
                        <TextInput
                            id="university"
                            className="mt-1 block w-full"
                            value={data.university}
                            onChange={(e) =>
                                setData('university', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.university}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="major" value="Jurusan" />
                        <TextInput
                            id="major"
                            className="mt-1 block w-full"
                            value={data.major}
                            onChange={(e) => setData('major', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.major} />
                    </div>
                    <div>
                        <InputLabel htmlFor="division" value="Divisi" />
                        <TextInput
                            id="division"
                            className="mt-1 block w-full"
                            value={data.division}
                            onChange={(e) =>
                                setData('division', e.target.value)
                            }
                        />
                        <InputError className="mt-2" message={errors.division} />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="start_date"
                            value="Tanggal Mulai"
                        />
                        <TextInput
                            id="start_date"
                            type="date"
                            className="mt-1 block w-full"
                            value={data.start_date}
                            onChange={(e) =>
                                setData('start_date', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.start_date}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="end_date" value="Tanggal Selesai" />
                        <TextInput
                            id="end_date"
                            type="date"
                            className="mt-1 block w-full"
                            value={data.end_date}
                            onChange={(e) =>
                                setData('end_date', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.end_date}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="status" value="Status" />
                        <SelectInput
                            id="status"
                            className="mt-1 block w-full"
                            value={data.status}
                            onChange={(e) => setData('status', e.target.value)}
                        >
                            {Object.entries(internStatusLabels).map(
                                ([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ),
                            )}
                        </SelectInput>
                        <InputError className="mt-2" message={errors.status} />
                    </div>
                </div>
            </section>

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route('admin.interns.index')}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? 'Simpan Perubahan' : 'Tambah Peserta'}
                </PrimaryButton>
            </div>
        </form>
    );
}
