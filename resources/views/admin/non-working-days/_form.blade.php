@php
    $nonWorkingDay ??= null;
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="date" :value="__('Tanggal')" />
        <x-text-input id="date" name="date" type="date" class="block mt-1 w-full"
                      :value="old('date', $nonWorkingDay?->date?->format('Y-m-d'))" required autofocus />
        <x-input-error :messages="$errors->get('date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" :value="__('Jenis')" />
        <select id="type" name="type" required
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">{{ __('-- Pilih Jenis --') }}</option>
            @foreach (\App\Models\NonWorkingDay::typeLabels() as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $nonWorkingDay?->type) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="name" :value="__('Nama')" />
        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
                      :value="old('name', $nonWorkingDay?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('admin.non-working-days.index') }}">
        <x-secondary-button type="button">{{ __('Batal') }}</x-secondary-button>
    </a>
    <x-primary-button>{{ $submitLabel ?? __('Save') }}</x-primary-button>
</div>
