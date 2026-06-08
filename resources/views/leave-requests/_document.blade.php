{{-- Document-like leave request detail. Expects $leaveRequest. --}}
<dl class="divide-y divide-gray-100">
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Nomor Pengajuan') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2 font-medium">{{ $leaveRequest->request_number }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Tanggal Pengajuan') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->created_at->format('d M Y, H:i') }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Jenis Pengajuan') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->typeLabel() }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Alasan') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2 whitespace-pre-line">{{ $leaveRequest->reason }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Tanggal Awal') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->start_date->format('d M Y') }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Tanggal Akhir') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->end_date->format('d M Y') }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Lama Hari') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->total_days }} {{ __('hari') }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Kontak / Telepon') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->contact_phone ?: '—' }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Alamat') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2 whitespace-pre-line">{{ $leaveRequest->address ?: '—' }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Status Persetujuan') }}</dt>
        <dd class="text-sm sm:col-span-2">
            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $leaveRequest->statusBadgeClass() }}">
                {{ $leaveRequest->statusLabel() }}
            </span>
        </dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">
            {{ $leaveRequest->status === 'rejected' ? __('Ditolak Oleh') : __('Disetujui Oleh') }}
        </dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $leaveRequest->approver?->name ?? '—' }}</dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Tanggal Persetujuan') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2">
            {{ $leaveRequest->approved_at?->format('d M Y, H:i') ?? '—' }}
        </dd>
    </div>
    <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
        <dt class="text-sm font-medium text-gray-500">{{ __('Catatan Admin') }}</dt>
        <dd class="text-sm text-gray-900 sm:col-span-2 whitespace-pre-line">{{ $leaveRequest->admin_note ?: '—' }}</dd>
    </div>
</dl>
