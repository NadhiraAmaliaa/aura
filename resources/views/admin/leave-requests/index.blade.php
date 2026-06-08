<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengajuan Izin / Sakit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-100 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.leave-requests.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <x-input-label for="search" :value="__('Cari Nomor / Nama')" />
                            <x-text-input id="search" name="search" type="text" class="block mt-1 w-full"
                                          :value="request('search')" placeholder="{{ __('Nomor atau nama...') }}" />
                        </div>
                        <div>
                            <x-input-label for="type" :value="__('Jenis')" />
                            <select id="type" name="type"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Semua Jenis') }}</option>
                                <option value="izin" @selected(request('type') === 'izin')>{{ __('Izin') }}</option>
                                <option value="sakit" @selected(request('type') === 'sakit')>{{ __('Sakit') }}</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Semua Status') }}</option>
                                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <x-primary-button>{{ __('Terapkan Filter') }}</x-primary-button>
                            @if (request()->hasAny(['search', 'type', 'status']))
                                <a href="{{ route('admin.leave-requests.index') }}">
                                    <x-secondary-button type="button">{{ __('Reset') }}</x-secondary-button>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($leaveRequests->isEmpty())
                        <p class="text-gray-500">{{ __('Belum ada pengajuan.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-4 py-3">{{ __('Nomor Pengajuan') }}</th>
                                        <th class="px-4 py-3">{{ __('Nama') }}</th>
                                        <th class="px-4 py-3">{{ __('Jenis') }}</th>
                                        <th class="px-4 py-3">{{ __('Tanggal') }}</th>
                                        <th class="px-4 py-3">{{ __('Lama Hari') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($leaveRequests as $leaveRequest)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $leaveRequest->request_number }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ $leaveRequest->user?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ $leaveRequest->typeLabel() }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                                {{ $leaveRequest->start_date->format('d M Y') }}
                                                &ndash;
                                                {{ $leaveRequest->end_date->format('d M Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $leaveRequest->total_days }} {{ __('hari') }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $leaveRequest->statusBadgeClass() }}">
                                                    {{ $leaveRequest->statusLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('admin.leave-requests.show', $leaveRequest) }}"
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('Detail') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $leaveRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
