import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import { nonWorkingDayTypeLabels } from '@/lib/labels';
import { NonWorkingDay } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function NonWorkingDayForm({
    nonWorkingDay,
}: {
    nonWorkingDay?: NonWorkingDay;
}) {
    const isEdit = Boolean(nonWorkingDay);

    const { data, setData, post, put, processing, errors } = useForm({
        date: nonWorkingDay?.date ?? '',
        name: nonWorkingDay?.name ?? '',
        type: nonWorkingDay?.type ?? 'national_holiday',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEdit && nonWorkingDay) {
            put(route('admin.non-working-days.update', nonWorkingDay.id));
        } else {
            post(route('admin.non-working-days.store'));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="date" value="Tanggal" />
                <TextInput
                    id="date"
                    type="date"
                    className="mt-1 block w-full"
                    value={data.date}
                    onChange={(e) => setData('date', e.target.value)}
                />
                <InputError className="mt-2" message={errors.date} />
            </div>

            <div>
                <InputLabel htmlFor="name" value="Nama Hari Libur" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel htmlFor="type" value="Jenis" />
                <SelectInput
                    id="type"
                    className="mt-1 block w-full"
                    value={data.type}
                    onChange={(e) =>
                        setData(
                            'type',
                            e.target.value as NonWorkingDay['type'],
                        )
                    }
                >
                    {Object.entries(nonWorkingDayTypeLabels).map(
                        ([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ),
                    )}
                </SelectInput>
                <InputError className="mt-2" message={errors.type} />
            </div>

            <div className="flex items-center justify-end gap-3">
                <Link
                    href={route('admin.non-working-days.index')}
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Batal
                </Link>
                <PrimaryButton disabled={processing}>
                    {isEdit ? 'Simpan Perubahan' : 'Tambah Hari Libur'}
                </PrimaryButton>
            </div>
        </form>
    );
}
