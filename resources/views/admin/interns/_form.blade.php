@php
    $intern ??= null;
    $userName = old('name', $intern?->user->name);
    $userEmail = old('email', $intern?->user->email);
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    {{-- Account --}}
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
                      :value="$userName" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full"
                      :value="$userEmail" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$intern ? __('Password (leave blank to keep current)') : __('Password')" />
        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full"
                      :required="! $intern" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                      class="block mt-1 w-full" :required="! $intern" autocomplete="new-password" />
    </div>

    {{-- Program --}}
    <div>
        <x-input-label for="intern_program_id" :value="__('Program Magang')" />
        <select id="intern_program_id" name="intern_program_id" required
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">{{ __('-- Select Program --') }}</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}"
                    @selected(old('intern_program_id', $intern?->intern_program_id) == $program->id)>
                    {{ $program->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('intern_program_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nim" :value="__('NIM')" />
        <x-text-input id="nim" name="nim" type="text" class="block mt-1 w-full"
                      :value="old('nim', $intern?->nim)" required />
        <x-input-error :messages="$errors->get('nim')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="block mt-1 w-full"
                      :value="old('phone', $intern?->phone)" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="university" :value="__('University')" />
        <x-text-input id="university" name="university" type="text" class="block mt-1 w-full"
                      :value="old('university', $intern?->university)" required />
        <x-input-error :messages="$errors->get('university')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="major" :value="__('Major')" />
        <x-text-input id="major" name="major" type="text" class="block mt-1 w-full"
                      :value="old('major', $intern?->major)" required />
        <x-input-error :messages="$errors->get('major')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="division" :value="__('Division')" />
        <x-text-input id="division" name="division" type="text" class="block mt-1 w-full"
                      :value="old('division', $intern?->division)" required />
        <x-input-error :messages="$errors->get('division')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="start_date" :value="__('Start Date')" />
        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full"
                      :value="old('start_date', $intern?->start_date?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="end_date" :value="__('End Date')" />
        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full"
                      :value="old('end_date', $intern?->end_date?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" required
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach (['active', 'inactive', 'completed'] as $status)
                <option value="{{ $status }}" @selected(old('status', $intern?->status) === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('admin.interns.index') }}">
        <x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button>
    </a>
    <x-primary-button>{{ $submitLabel ?? __('Save') }}</x-primary-button>
</div>
