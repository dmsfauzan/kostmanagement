<div class="space-y-6">
    <x-ui.page-header title="Metode Pembayaran" description="Metode yang tersedia untuk pembayaran penghuni." />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card padding="p-0">
                <div class="border-b border-gray-100 p-4 dark:border-gray-700">
                    <div class="sm:w-80"><x-ui.search-input model="search" placeholder="Cari metode..." /></div>
                </div>

                @if ($methods->isEmpty())
                    <div class="p-6"><x-ui.empty-state title="Belum ada metode" description="Tambahkan metode pembayaran." /></div>
                @else
                    <x-ui.table :headings="['Nama', 'Tipe', 'Akun', 'Status', '']">
                        @foreach ($methods as $method)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $method->name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $method->type->label() }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    @if ($method->account_number)
                                        {{ $method->account_name }} · {{ $method->account_number }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$method->is_active ? 'success' : 'neutral'">{{ $method->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('payment.create')
                                            <button type="button" wire:click="toggleActive({{ $method->id }})" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Ubah Status</button>
                                            <x-ui.confirm-dialog title="Hapus metode" :message="'Hapus metode '.$method->name.'?'" :action="'delete('.$method->id.')'">
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
                    <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $methods->links() }}</div>
                @endif
            </x-ui.card>
        </div>

        @can('payment.create')
            <x-ui.card>
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Tambah Metode</h3>
                <form wire:submit="create" class="space-y-4">
                    <x-ui.input name="name" label="Nama" wire:model="name" :required="true" placeholder="Mis. Transfer BCA" />
                    <x-ui.select name="type" label="Tipe" wire:model="type" :required="true" :options="collect($types)->mapWithKeys(fn($t) => [$t->value => $t->label()])->all()" :placeholder="null" />
                    <x-ui.input name="account_name" label="Nama Pemilik Akun" wire:model="account_name" />
                    <x-ui.input name="account_number" label="Nomor Akun" wire:model="account_number" />
                    <x-ui.textarea name="instructions" label="Instruksi" wire:model="instructions" />
                    <x-ui.button type="submit" class="w-full">Tambah</x-ui.button>
                </form>
            </x-ui.card>
        @endcan
    </div>
</div>
