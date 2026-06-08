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
                <a href="{{ route('admin.leave-requests.index') }}">
                    <x-secondary-button type="button">{{ __('Kembali') }}</x-secondary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Intern identity --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-base font-semibold mb-4">{{ __('Data Peserta') }}</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-gray-500">{{ __('Nama') }}</dt>
                            <dd class="text-gray-900">{{ $leaveRequest->user?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('NIM') }}</dt>
                            <dd class="text-gray-900">{{ $leaveRequest->user?->intern?->nim ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Program Magang') }}</dt>
                            <dd class="text-gray-900">{{ $leaveRequest->user?->intern?->internProgram?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Divisi') }}</dt>
                            <dd class="text-gray-900">{{ $leaveRequest->user?->intern?->division ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Document detail --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include('leave-requests._document', ['leaveRequest' => $leaveRequest])
                </div>
            </div>

            {{-- Approval actions --}}
            @if ($leaveRequest->status === 'pending')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-base font-semibold mb-4">{{ __('Tindakan Persetujuan') }}</h3>

                        <div class="mb-4">
                            <x-input-label for="admin_note" :value="__('Catatan Admin')" />
                            <textarea id="admin_note" name="admin_note" form="approve-form" rows="3"
                                      placeholder="{{ __('Opsional') }}"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('admin_note') }}</textarea>
                            <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <form id="approve-form" method="POST"
                                  action="{{ route('admin.leave-requests.approve', $leaveRequest) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button>{{ __('Setujui') }}</x-primary-button>
                            </form>

                            <form method="POST"
                                  action="{{ route('admin.leave-requests.reject', $leaveRequest) }}"
                                  onsubmit="document.getElementById('reject-note').value = document.getElementById('admin_note').value;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" id="reject-note" name="admin_note">
                                <x-danger-button>{{ __('Tolak') }}</x-danger-button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
