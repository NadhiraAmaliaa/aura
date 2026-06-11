<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.attendances.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- Search --}}
                        <div>
                            <x-input-label for="search" :value="__('Cari Nama / NIM')" />
                            <x-text-input id="search" name="search" type="text" class="block mt-1 w-full"
                                          :value="request('search')" placeholder="{{ __('Nama atau NIM...') }}" />
                        </div>

                        {{-- Date --}}
                        <div>
                            <x-input-label for="date" :value="__('Tanggal')" />
                            <x-text-input id="date" name="date" type="date" class="block mt-1 w-full"
                                          :value="request('date')" />
                        </div>

                        {{-- Program --}}
                        <div>
                            <x-input-label for="program" :value="__('Program Magang')" />
                            <select id="program" name="program"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Semua Program') }}</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}" @selected(request('program') == $program->id)>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Semua Status') }}</option>
                                @foreach (['present' => 'Hadir', 'late' => 'Terlambat', 'sick' => 'Sakit', 'permission' => 'Izin', 'absent' => 'Alpha'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                            <x-primary-button>{{ __('Terapkan Filter') }}</x-primary-button>
                            @if (request()->hasAny(['search', 'date', 'program', 'status']))
                                <a href="{{ route('admin.attendances.index') }}">
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
                    @if ($attendances->isEmpty())
                        <p class="text-gray-500">{{ __('Belum ada data absensi.') }}</p>
                    @else
                        <div class="mb-3 text-sm text-gray-500">
                            {{ __('Menampilkan :from–:to dari :total data', [
                                'from' => $attendances->firstItem(),
                                'to'   => $attendances->lastItem(),
                                'total' => $attendances->total(),
                            ]) }}
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-4 py-3">{{ __('Tanggal') }}</th>
                                        <th class="px-4 py-3">{{ __('Nama') }}</th>
                                        <th class="px-4 py-3">{{ __('NIM') }}</th>
                                        <th class="px-4 py-3">{{ __('Program Magang') }}</th>
                                        <th class="px-4 py-3">{{ __('Check In') }}</th>
                                        <th class="px-4 py-3">{{ __('Check Out') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3">{{ __('Catatan') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($attendances as $attendance)
                                        @php
                                            $intern = $attendance->user?->intern;
                                            $statusMap = [
                                                'present'    => ['label' => 'Hadir',       'class' => 'bg-green-100 text-green-800'],
                                                'late'       => ['label' => 'Terlambat',   'class' => 'bg-yellow-100 text-yellow-800'],
                                                'sick'       => ['label' => 'Sakit',        'class' => 'bg-orange-100 text-orange-800'],
                                                'permission' => ['label' => 'Izin',         'class' => 'bg-blue-100 text-blue-800'],
                                                'absent'     => ['label' => 'Alpha',       'class' => 'bg-red-100 text-red-800'],
                                            ];
                                            $badge = $statusMap[$attendance->status] ?? ['label' => ucfirst($attendance->status), 'class' => 'bg-gray-100 text-gray-800'];
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $attendance->attendance_date->format('d M Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-900">
                                                {{ $attendance->user?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $intern?->nim ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $intern?->internProgram?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $attendance->check_in_time?->format('H:i') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $attendance->check_out_time?->format('H:i') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $badge['class'] }}">
                                                    {{ $badge['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
                                                {{ $attendance->notes ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <a href="{{ route('admin.attendances.edit', $attendance) }}"
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('Koreksi') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
