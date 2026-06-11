<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Hari Libur') }}
            </h2>
            <a href="{{ route('admin.non-working-days.create') }}">
                <x-primary-button type="button">{{ __('Tambah Hari Libur') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($nonWorkingDays->isEmpty())
                        <p class="text-gray-500">{{ __('Belum ada data hari libur.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-4 py-3">{{ __('Tanggal') }}</th>
                                        <th class="px-4 py-3">{{ __('Nama') }}</th>
                                        <th class="px-4 py-3">{{ __('Jenis') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($nonWorkingDays as $nonWorkingDay)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ $nonWorkingDay->date->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $nonWorkingDay->name }}</td>
                                            <td class="px-4 py-3">
                                                <span @class([
                                                    'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                                    'bg-red-100 text-red-800' => $nonWorkingDay->type === \App\Models\NonWorkingDay::TYPE_NATIONAL_HOLIDAY,
                                                    'bg-blue-100 text-blue-800' => $nonWorkingDay->type === \App\Models\NonWorkingDay::TYPE_COLLECTIVE_LEAVE,
                                                    'bg-purple-100 text-purple-800' => $nonWorkingDay->type === \App\Models\NonWorkingDay::TYPE_COMPANY_HOLIDAY,
                                                ])>
                                                    {{ $nonWorkingDay->typeLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('admin.non-working-days.edit', $nonWorkingDay) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                                    <form method="POST" action="{{ route('admin.non-working-days.destroy', $nonWorkingDay) }}"
                                                          onsubmit="return confirm('{{ __('Hapus hari libur ini?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            {{ __('Hapus') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $nonWorkingDays->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
