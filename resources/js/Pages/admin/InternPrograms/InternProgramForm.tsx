import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextareaInput from '@/Components/TextareaInput';
import TextInput from '@/Components/TextInput';
import { InternProgram } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface ProgramFormData {
    name: string;
    description: string;
    [key: string]: string;
}

export default function InternProgramForm({
    program,
}: {
    program?: InternProgram;
}) {
    const isEdit = Boolean(program);

    const { data, setData, post, put, processing, errors } =
        useForm<ProgramFormData>({
            name: program?.name ?? '',
            description: program?.description ?? '',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && program) {
            put(route('admin.intern-programs.update', program.id));
        } else {
            post(route('admin.intern-programs.store'));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="Nama Program" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    isFocused
                    onChange={(e) => setData('name', e.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel
                    htmlFor="description"
                    value="Deskripsi (opsional)"
                />
                <TextareaInput
                    id="description"
                    className="mt-1 block w-full"
                    rows={4}
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                />
                <InputError className="mt-2" message={errors.description} />
            </div>

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route('admin.intern-programs.index')}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? 'Simpan Perubahan' : 'Tambah Program'}
                </PrimaryButton>
            </div>
        </form>
    );
}
