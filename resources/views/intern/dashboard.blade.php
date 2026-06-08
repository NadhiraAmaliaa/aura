<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Intern Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __('Welcome,') }} {{ auth()->user()->name }}. {{ __("You're logged in as an intern.") }}

                    <div class="mt-6 border-t border-gray-100 pt-6">
                        <h3 class="text-base font-semibold mb-2">
                            {{ __("Today's Attendance") }} &middot; {{ now()->format('d M Y') }}
                        </h3>

                        @if (! $todayAttendance)
                            <p class="text-gray-600">{{ __('You have not checked in today.') }}</p>
                        @elseif (! $todayAttendance->check_out_time)
                            <p class="text-gray-600">
                                {{ __('Checked in at') }}
                                <span class="font-medium">{{ $todayAttendance->check_in_time?->format('H:i') }}</span>.
                                {{ __('Not checked out yet.') }}
                            </p>
                        @else
                            <p class="text-gray-600">
                                {{ __('Completed') }} —
                                {{ __('In') }}: {{ $todayAttendance->check_in_time?->format('H:i') }},
                                {{ __('Out') }}: {{ $todayAttendance->check_out_time?->format('H:i') }}
                            </p>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('intern.attendance.index') }}">
                                <x-primary-button type="button">{{ __('Go to Attendance') }}</x-primary-button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
