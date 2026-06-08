<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __('Selamat datang, Administrator. Anda memiliki akses admin.') }}

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('admin.interns.index') }}">
                            <x-primary-button type="button">{{ __('Kelola Peserta Magang') }}</x-primary-button>
                        </a>
                        <a href="{{ route('admin.attendances.index') }}">
                            <x-secondary-button type="button">{{ __('Monitoring Absensi') }}</x-secondary-button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
