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

                        @if (! $todayAttendance)
                            <p class="text-gray-600">{{ __('Anda belum Check In hari ini.') }}</p>
                        @elseif (! $todayAttendance->check_out_time)
                            <p class="text-gray-600">
                                {{ __('Check In pada pukul') }}
                                <span class="font-medium">{{ $todayAttendance->check_in_time?->format('H:i') }}</span>.
                                {{ __('Belum Check Out.') }}
                            </p>
                        @else
                            <p class="text-gray-600">
                                {{ __('Selesai') }} —
                                {{ __('Masuk') }}: {{ $todayAttendance->check_in_time?->format('H:i') }},
                                {{ __('Keluar') }}: {{ $todayAttendance->check_out_time?->format('H:i') }}
                            </p>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('intern.attendance.index') }}">
                                <x-primary-button type="button">{{ __('Buka Absensi') }}</x-primary-button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
