<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Rekap Absensi') }}
            </h2>
            <a href="{{ route('admin.attendances.index') }}">
                <x-secondary-button type="button">{{ __('Monitoring Absensi') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.attendances.recap') }}"
                          class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full"
                                          :value="request('start_date', $recap['start_date']->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_date" :value="__('Tanggal Akhir')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full"
                                          :value="request('end_date', $recap['end_date']->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>

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

                        <div class="flex items-end gap-2">
                            <x-primary-button>{{ __('Tampilkan') }}</x-primary-button>
                            @if (request()->hasAny(['start_date', 'end_date', 'program']))
                                <a href="{{ route('admin.attendances.recap') }}">
                                    <x-secondary-button type="button">{{ __('Reset') }}</x-secondary-button>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                        <span>
                            {{ __('Periode') }}:
                            <span class="font-medium text-gray-900">
                                {{ $recap['start_date']->translatedFormat('d M Y') }}
                                &ndash;
                                {{ $recap['end_date']->translatedFormat('d M Y') }}
                            </span>
                        </span>
                        <span>
                            {{ __('Hari Kerja Efektif') }}:
                            <span class="font-medium text-gray-900">{{ $recap['effective_working_days'] }}</span>
                        </span>
                    </div>

                    @if (empty($recap['rows']))
                        <p class="text-gray-500">{{ __('Belum ada data peserta magang.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-3 py-3">{{ __('Nama') }}</th>
                                        <th class="px-3 py-3">{{ __('NIM') }}</th>
                                        <th class="px-3 py-3">{{ __('Program') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('Hari Kerja Efektif') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('WFO') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('Izin') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('Tidak Absen') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('Terlambat Datang') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('Tidak CO') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('% Terlambat Datang') }}</th>
                                        <th class="px-3 py-3 text-right">{{ __('% Tidak Absen') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($recap['rows'] as $row)
                                        <tr>
                                            <td class="px-3 py-3 font-medium text-gray-900">{{ $row['intern']->user->name }}</td>
                                            <td class="px-3 py-3 text-gray-600">{{ $row['intern']->nim }}</td>
                                            <td class="px-3 py-3 text-gray-600">{{ $row['intern']->internProgram?->name }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ $row['effective_working_days'] }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ $row['wfo'] }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ $row['izin'] }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ $row['tidak_absen'] }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ $row['terlambat'] }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ $row['tidak_co'] }}</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format($row['persen_terlambat'], 1) }}%</td>
                                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format($row['persen_tidak_absen'], 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
