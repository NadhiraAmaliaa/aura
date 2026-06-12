<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Koreksi Status Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-gray-500">{{ __('Nama') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $attendance->user?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('NIM') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $attendance->user?->intern?->nim ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Tanggal') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $attendance->attendance_date->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Jam Masuk / Keluar') }}</dt>
                            <dd class="font-medium text-gray-900">
                                {{ $attendance->check_in_time?->format('H:i') ?? '—' }}
                                &middot;
                                {{ $attendance->check_out_time?->format('H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Mode Kehadiran') }}</dt>
                            <dd class="font-medium text-gray-900">
                                @if ($attendance->workModeLabel())
                                    {{ $attendance->workModeLabel() }}
                                @else
                                    &mdash;
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <form method="POST" action="{{ route('admin.attendances.update', $attendance) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach (\App\Models\Attendance::statusLabels() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $attendance->status) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="work_mode" :value="__('Mode Kehadiran')" />
                            <select id="work_mode" name="work_mode"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('— Tidak ditentukan —') }}</option>
                                @foreach (\App\Models\Attendance::workModeLabels() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('work_mode', $attendance->work_mode) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('work_mode')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" :value="__('Catatan')" />
                            <textarea id="notes" name="notes" rows="3"
                                      placeholder="{{ __('Opsional — dasar koreksi, dokumentasi, atau kebijakan terkait.') }}"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $attendance->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>
                            <a href="{{ route('admin.attendances.index') }}">
                                <x-secondary-button type="button">{{ __('Batal') }}</x-secondary-button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
