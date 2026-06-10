<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengajuan Izin / Sakit') }}
            </h2>
            <a href="{{ route('intern.leave-requests.create') }}">
                <x-primary-button type="button">{{ __('Buat Pengajuan') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

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
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <a href="{{ route('intern.leave-requests.show', $leaveRequest) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    {{ __('Detail') }}
                                                </a>
                                                @if ($leaveRequest->status === 'approved')
                                                    <a href="{{ route('leave-requests.pdf', $leaveRequest) }}" target="_blank"
                                                       class="text-green-600 hover:text-green-900">
                                                        {{ __('Cetak PDF') }}
                                                    </a>
                                                @endif
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
