<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Peserta Magang') }}
            </h2>
            <a href="{{ route('admin.interns.create') }}">
                <x-primary-button type="button">{{ __('Tambah Peserta Magang') }}</x-primary-button>
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
                    @if ($interns->isEmpty())
                        <p class="text-gray-500">{{ __('Belum ada data peserta magang.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-4 py-3">{{ __('Nama') }}</th>
                                        <th class="px-4 py-3">{{ __('Email') }}</th>
                                        <th class="px-4 py-3">{{ __('NIM') }}</th>
                                        <th class="px-4 py-3">{{ __('Program Magang') }}</th>
                                        <th class="px-4 py-3">{{ __('Divisi') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($interns as $intern)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $intern->user->name }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $intern->user->email }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $intern->nim }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $intern->internProgram->name }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $intern->division }}</td>
                                            <td class="px-4 py-3">
                                                <span @class([
                                                    'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                                    'bg-green-100 text-green-800' => $intern->status === 'active',
                                                    'bg-gray-100 text-gray-800' => $intern->status === 'inactive',
                                                    'bg-blue-100 text-blue-800' => $intern->status === 'completed',
                                                ])>
                                                    {{ ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'completed' => 'Selesai'][$intern->status] ?? ucfirst($intern->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('admin.interns.edit', $intern) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                                    <form method="POST" action="{{ route('admin.interns.destroy', $intern) }}"
                                                          onsubmit="return confirm('{{ __('Hapus peserta magang dan akun pengguna ini?') }}');">
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
                            {{ $interns->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
