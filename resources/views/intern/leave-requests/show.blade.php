<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Pengajuan') }}
            </h2>
            <div class="flex items-center gap-2">
                @if ($leaveRequest->status === 'approved')
                    <a href="{{ route('leave-requests.pdf', $leaveRequest) }}" target="_blank">
                        <x-primary-button type="button">{{ __('Cetak PDF') }}</x-primary-button>
                    </a>
                @endif
                <a href="{{ route('intern.leave-requests.index') }}">
                    <x-secondary-button type="button">{{ __('Kembali') }}</x-secondary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include('leave-requests._document', ['leaveRequest' => $leaveRequest])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
