<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absensi Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Today's status --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">
                        {{ __('Hari Ini') }} &middot; {{ now()->format('l, d M Y') }}
                    </h3>

                    <p class="text-sm text-gray-500 mb-4">
                        @if ($expectedCheckOut)
                            {{ __('Jam pulang yang diharapkan hari ini: :time (hanya informasi).', ['time' => $expectedCheckOut]) }}
                        @else
                            {{ __('Akhir pekan — absensi lembur atau kegiatan khusus diperbolehkan.') }}
                        @endif
                    </p>

                    @if ($todayLeave)
                        <div class="rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            {{ __('Hari ini Anda tercatat :type berdasarkan pengajuan yang telah disetujui. Anda tidak dapat melakukan Check In.', ['type' => $todayLeave->typeLabel()]) }}
                        </div>
                    @elseif (! $todayAttendance)
                        <p class="text-gray-600 mb-4">{{ __('Anda belum Check In hari ini. Pilih mode kehadiran terlebih dahulu:') }}</p>

                        <form method="POST" action="{{ route('intern.attendance.check-in') }}" data-geo-form id="check-in-form">
                            @csrf
                            <input type="hidden" name="latitude" data-geo-lat>
                            <input type="hidden" name="longitude" data-geo-lng>

                            <div class="space-y-3 max-w-md">
                                {{-- WFO --}}
                                <label class="block rounded-lg border border-gray-200 p-4 cursor-pointer hover:bg-gray-50">
                                    <span class="flex items-center gap-2 font-medium text-gray-900">
                                        <input type="radio" name="work_mode" value="{{ \App\Models\Attendance::WORK_MODE_WFO }}"
                                               class="text-indigo-600 focus:ring-indigo-500" checked>
                                        {{ __('WFO (Bekerja dari Kantor)') }}
                                    </span>
                                    <p class="mt-2 ml-6 text-xs text-gray-500">{{ __('Berlaku batas waktu & status terlambat. Validasi lokasi kantor menyusul.') }}</p>
                                </label>

                                {{-- WFH --}}
                                <label class="block rounded-lg border border-gray-200 p-4 cursor-pointer hover:bg-gray-50">
                                    <span class="flex items-center gap-2 font-medium text-gray-900">
                                        <input type="radio" name="work_mode" value="{{ \App\Models\Attendance::WORK_MODE_WFH }}"
                                               class="text-indigo-600 focus:ring-indigo-500">
                                        {{ __('WFH (Bekerja dari Rumah)') }}
                                    </span>
                                    <p class="mt-2 ml-6 text-xs text-gray-500">{{ __('Berlaku batas waktu & status terlambat. Tanpa validasi lokasi.') }}</p>
                                </label>

                                {{-- Dinas --}}
                                <label class="block rounded-lg border border-gray-200 p-4 cursor-pointer hover:bg-gray-50">
                                    <span class="flex items-center gap-2 font-medium text-gray-900">
                                        <input type="radio" name="work_mode" value="{{ \App\Models\Attendance::WORK_MODE_DINAS }}"
                                               class="text-indigo-600 focus:ring-indigo-500">
                                        {{ __('Dinas (Tugas Luar)') }}
                                    </span>
                                    <p class="mt-2 ml-6 text-xs text-gray-500">{{ __('Dapat Check In kapan saja & di mana saja. Tanpa status terlambat.') }}</p>
                                </label>
                            </div>

                            <x-input-error :messages="$errors->get('work_mode')" class="mt-2" />

                            <div class="mt-4">
                                <x-primary-button>{{ __('Check In') }}</x-primary-button>
                            </div>
                        </form>
                    @elseif (! $todayAttendance->check_out_time)
                        <p class="text-gray-600 mb-2">
                            {{ __('Check In pada pukul') }}
                            <span class="font-medium">{{ $todayAttendance->check_in_time?->format('H:i') }}</span>
                            <span class="ml-2 inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $todayAttendance->statusBadgeClass() }}">
                                {{ $todayAttendance->statusLabel() }}
                            </span>
                            @if ($todayAttendance->workModeLabel())
                                <span class="ml-1 inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    {{ $todayAttendance->workModeLabel() }}
                                </span>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('intern.attendance.check-out') }}" data-geo-form>
                            @csrf
                            <input type="hidden" name="latitude" data-geo-lat>
                            <input type="hidden" name="longitude" data-geo-lng>
                            <x-primary-button>{{ __('Check Out') }}</x-primary-button>
                        </form>
                    @else
                        <div class="rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            {{ __('Absensi hari ini telah selesai.') }}
                            <div class="mt-1 text-blue-700">
                                {{ __('Masuk') }}: {{ $todayAttendance->check_in_time?->format('H:i') }}
                                &middot;
                                {{ __('Keluar') }}: {{ $todayAttendance->check_out_time?->format('H:i') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- History --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Riwayat Absensi') }}</h3>

                    @if ($history->isEmpty())
                        <p class="text-gray-500">{{ __('Belum ada data absensi.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-4 py-3">{{ __('Tanggal') }}</th>
                                        <th class="px-4 py-3">{{ __('Mode') }}</th>
                                        <th class="px-4 py-3">{{ __('Check In') }}</th>
                                        <th class="px-4 py-3">{{ __('Check Out') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($history as $record)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ $record->attendance_date->format('d M Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                @if ($record->workModeLabel())
                                                    {{ $record->workModeLabel() }}
                                                @else
                                                    &mdash;
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $record->check_in_time?->format('H:i') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $record->check_out_time?->format('H:i') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $record->statusBadgeClass() }}">
                                                    {{ $record->statusLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $history->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-geo-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var latField = form.querySelector('[data-geo-lat]');
                    var lngField = form.querySelector('[data-geo-lng]');

                    // Already captured or geolocation unavailable: submit as-is.
                    if (!navigator.geolocation || form.dataset.geoDone) {
                        return;
                    }

                    event.preventDefault();
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            latField.value = position.coords.latitude;
                            lngField.value = position.coords.longitude;
                            form.dataset.geoDone = '1';
                            form.submit();
                        },
                        function () {
                            // Permission denied or error: submit without coordinates.
                            form.dataset.geoDone = '1';
                            form.submit();
                        }
                    );
                });
            });
        </script>
    @endpush
</x-app-layout>
