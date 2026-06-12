<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Peserta Magang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __('Selamat datang,') }} {{ auth()->user()->name }}. {{ __('Anda masuk sebagai peserta magang.') }}

                    <div class="mt-6 border-t border-gray-100 pt-6">
                        <h3 class="text-base font-semibold mb-2">
                            {{ __('Absensi Hari Ini') }} &middot; {{ now()->format('d M Y') }}
                        </h3>

                        @if ($todayLeave)
                            <div class="rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800">
                                {{ __('Hari ini Anda tercatat :type berdasarkan pengajuan yang telah disetujui. Anda tidak perlu melakukan Check In.', ['type' => $todayLeave->typeLabel()]) }}
                            </div>
                        @elseif (! $todayAttendance)
                            <p class="text-gray-600">{{ __('Anda belum Check In hari ini.') }}</p>
                        @elseif (! $todayAttendance->check_out_time)
                            <p class="text-gray-600">
                                {{ __('Check In pada pukul') }}
                                <span class="font-medium">{{ $todayAttendance->check_in_time?->format('H:i') }}</span>
                                @if ($todayAttendance->workModeLabel())
                                    <span class="ml-1 inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                        {{ $todayAttendance->workModeLabel() }}
                                    </span>
                                @endif
                                {{ __('Belum Check Out.') }}
                            </p>
                        @else
                            <p class="text-gray-600">
                                {{ __('Selesai') }} —
                                {{ __('Masuk') }}: {{ $todayAttendance->check_in_time?->format('H:i') }},
                                {{ __('Keluar') }}: {{ $todayAttendance->check_out_time?->format('H:i') }}
                            </p>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('intern.attendance.index') }}">
                                <x-primary-button type="button">{{ __('Buka Absensi') }}</x-primary-button>
                            </a>
                            <a href="{{ route('intern.leave-requests.index') }}">
                                <x-secondary-button type="button">{{ __('Pengajuan Izin / Sakit') }}</x-secondary-button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
