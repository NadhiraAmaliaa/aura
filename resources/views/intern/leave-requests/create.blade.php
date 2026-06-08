<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Pengajuan Izin / Sakit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('intern.leave-requests.store') }}" class="space-y-6">
                        @csrf

                        {{-- Type --}}
                        <div>
                            <x-input-label for="type" :value="__('Jenis Pengajuan')" />
                            <select id="type" name="type"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="izin" @selected(old('type') === 'izin')>{{ __('Izin') }}</option>
                                <option value="sakit" @selected(old('type') === 'sakit')>{{ __('Sakit') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        {{-- Dates --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="start_date" :value="__('Tanggal Awal')" />
                                <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full"
                                              :value="old('start_date')" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('Tanggal Akhir')" />
                                <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full"
                                              :value="old('end_date')" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Reason --}}
                        <div>
                            <x-input-label for="reason" :value="__('Alasan')" />
                            <textarea id="reason" name="reason" rows="3"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        {{-- Contact phone --}}
                        <div>
                            <x-input-label for="contact_phone" :value="__('Kontak / Telepon')" />
                            <x-text-input id="contact_phone" name="contact_phone" type="text" class="block mt-1 w-full"
                                          :value="old('contact_phone')" placeholder="{{ __('Opsional') }}" />
                            <x-input-error :messages="$errors->get('contact_phone')" class="mt-2" />
                        </div>

                        {{-- Address --}}
                        <div>
                            <x-input-label for="address" :value="__('Alamat')" />
                            <textarea id="address" name="address" rows="2"
                                      placeholder="{{ __('Opsional') }}"
                                      class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address') }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Kirim Pengajuan') }}</x-primary-button>
                            <a href="{{ route('intern.leave-requests.index') }}">
                                <x-secondary-button type="button">{{ __('Batal') }}</x-secondary-button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
