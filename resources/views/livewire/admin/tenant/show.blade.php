<div class="space-y-6">
    <x-ui.page-header :title="$tenant->full_name" :description="$tenant->property?->name ?? 'Penghuni'">
        <x-slot:actions>
            <x-ui.button :href="route('admin.tenants')" variant="secondary" wire:navigate>Kembali</x-ui.button>
            @can('tenant.update')
                <x-ui.button :href="route('admin.tenants.edit', $tenant)" wire:navigate>Ubah</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Data Pribadi</h3></x-slot:header>
            <dl class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    'Nama Lengkap' => $tenant->full_name,
                    'NIK' => $tenant->nik,
                    'Jenis Kelamin' => $tenant->gender?->label(),
                    'Tanggal Lahir' => $tenant->birth_date?->translatedFormat('d F Y'),
                    'Telepon' => $tenant->phone,
                    'Email' => $tenant->email,
                    'Alamat' => $tenant->address,
                    'Kontak Darurat' => $tenant->emergency_name ? $tenant->emergency_name.' ('.$tenant->emergency_relationship.') — '.$tenant->emergency_phone : null,
                    'Kendaraan' => $tenant->vehicle_type ? $tenant->vehicle_type.' · '.$tenant->vehicle_number : null,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Akun Portal</h3></x-slot:header>
            <div class="space-y-3">
                <x-ui.status-badge :status="$tenant->status" />
                @if ($tenant->user)
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $tenant->user->email }}</p>
                    <x-ui.badge :variant="$tenant->user->isActive() ? 'success' : 'warning'">{{ $tenant->user->isActive() ? 'Aktif' : 'Menunggu aktivasi' }}</x-ui.badge>
                @else
                    <p class="text-sm text-gray-500">Belum ada akun. Klik kirim undangan untuk membuat akun portal.</p>
                @endif
                @can('tenant.update')
                    <x-ui.button wire:click="resendInvite" variant="secondary" class="w-full">{{ $tenant->user ? 'Kirim Ulang Undangan' : 'Buat & Kirim Undangan' }}</x-ui.button>
                @endcan
            </div>
        </x-ui.card>
    </div>

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Dokumen</h3></x-slot:header>

        @if ($tenant->documents->isNotEmpty())
            <x-ui.table :headings="['Jenis', 'Nomor', 'Diunggah', '']">
                @foreach ($tenant->documents as $document)
                    <tr>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $document->type->label() }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $document->number ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $document->created_at->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('tenant_document.view')
                                    <x-ui.button :href="route('admin.tenant-documents.download', $document)" variant="secondary" size="sm">Unduh</x-ui.button>
                                @endcan
                                @can('tenant_document.delete')
                                    <x-ui.confirm-dialog title="Hapus dokumen" message="Dokumen akan dihapus permanen." :action="'deleteDocument('.$document->id.')'">
                                        <x-slot:trigger>
                                            <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950">Hapus</button>
                                        </x-slot:trigger>
                                    </x-ui.confirm-dialog>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <p class="text-sm text-gray-500">Belum ada dokumen.</p>
        @endif

        @can('tenant_document.create')
            <form wire:submit="uploadDocument" class="mt-6 grid gap-4 border-t border-gray-100 pt-6 sm:grid-cols-3 dark:border-gray-700">
                <x-ui.select name="documentType" label="Jenis" wire:model="documentType" :required="true" :options="collect($documentTypes)->mapWithKeys(fn($t) => [$t->value => $t->label()])->all()" :placeholder="null" />
                <x-ui.input name="documentNumber" label="Nomor" wire:model="documentNumber" />
                <div class="sm:col-span-3">
                    <x-ui.file-upload model="document" label="Berkas" accept="image/*,.pdf" hint="JPG, PNG, WebP, atau PDF hingga 4 MB" />
                </div>
                <div class="sm:col-span-3">
                    <x-ui.button type="submit">Unggah Dokumen</x-ui.button>
                </div>
            </form>
        @endcan
    </x-ui.card>

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Kontrak</h3></x-slot:header>
        @if ($tenant->leases->isNotEmpty())
            <x-ui.table :headings="['Kode', 'Kamar', 'Periode', 'Sewa', 'Status', '']">
                @foreach ($tenant->leases as $lease)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $lease->code }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $lease->room?->number ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $lease->start_date->format('d M Y') }} – {{ $lease->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Support\Money::format($lease->rent_amount) }}</td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$lease->status" /></td>
                        <td class="px-4 py-3 text-right">
                            @can('lease.view')
                                <x-ui.button :href="route('admin.leases.show', $lease)" variant="secondary" size="sm" wire:navigate>Lihat</x-ui.button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <p class="text-sm text-gray-500">Belum ada kontrak sewa.</p>
        @endif
    </x-ui.card>
</div>
