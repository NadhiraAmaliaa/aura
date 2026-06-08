<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Attendance') }}
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
                        {{ __('Today') }} &middot; {{ now()->format('l, d M Y') }}
                    </h3>

                    @if (! $todayAttendance)
                        <p class="text-gray-600 mb-4">{{ __('You have not checked in today.') }}</p>
                        <form method="POST" action="{{ route('intern.attendance.check-in') }}" data-geo-form>
                            @csrf
                            <input type="hidden" name="latitude" data-geo-lat>
                            <input type="hidden" name="longitude" data-geo-lng>
                            <x-primary-button>{{ __('Check In') }}</x-primary-button>
                        </form>
                    @elseif (! $todayAttendance->check_out_time)
                        <p class="text-gray-600 mb-2">
                            {{ __('Checked in at') }}
                            <span class="font-medium">{{ $todayAttendance->check_in_time?->format('H:i') }}</span>
                        </p>
                        <form method="POST" action="{{ route('intern.attendance.check-out') }}" data-geo-form>
                            @csrf
                            <input type="hidden" name="latitude" data-geo-lat>
                            <input type="hidden" name="longitude" data-geo-lng>
                            <x-primary-button>{{ __('Check Out') }}</x-primary-button>
                        </form>
                    @else
                        <div class="rounded-md bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            {{ __('Attendance completed for today.') }}
                            <div class="mt-1 text-blue-700">
                                {{ __('In') }}: {{ $todayAttendance->check_in_time?->format('H:i') }}
                                &middot;
                                {{ __('Out') }}: {{ $todayAttendance->check_out_time?->format('H:i') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- History --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Attendance History') }}</h3>

                    @if ($history->isEmpty())
                        <p class="text-gray-500">{{ __('No attendance records yet.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="px-4 py-3">{{ __('Date') }}</th>
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
                                                {{ $record->check_in_time?->format('H:i') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $record->check_out_time?->format('H:i') ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($record->status) }}</td>
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
