<div class="space-y-6">
    <x-ui.page-header :title="$invoice->invoice_number" :description="$invoice->tenant?->full_name.' — '.$invoice->lease?->room?->number">
        <x-slot:actions>
            <x-ui.button :href="route('admin.invoices')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rincian Tagihan</h3></x-slot:header>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Status</dt>
                    <dd class="mt-1"><x-ui.status-badge :status="$invoice->status" /></dd>
                </div>
                @foreach ([
                    'Periode' => $invoice->billing_period ?? ($invoice->period_start?->format('d M Y').' – '.$invoice->period_end?->format('d M Y')),
                    'Terbit' => $invoice->issue_date->translatedFormat('d F Y'),
                    'Jatuh Tempo' => $invoice->due_date->translatedFormat('d F Y'),
                    'Kontrak' => $invoice->lease?->code,
                    'Kamar' => $invoice->lease?->room?->number,
                    'Properti' => $invoice->property?->name,
                    'Sisa Hari' => $invoice->daysUntilDue().' hari',
                ] as $label => $value)
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
                @if ($invoice->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Catatan</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $invoice->notes }}</dd>
                    </div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card>
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Pembayaran</h3></x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal</span><span class="font-medium text-gray-900 dark:text-white">{{ \App\Support\Money::format($invoice->subtotal) }}</span></div>
                @if ($invoice->discount_amount > 0)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Diskon</span><span class="font-medium text-success-600">-{{ \App\Support\Money::format($invoice->discount_amount) }}</span></div>
                @endif
                @if ($invoice->late_fee_amount > 0)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Denda</span><span class="font-medium text-warning-600">+{{ \App\Support\Money::format($invoice->late_fee_amount) }}</span></div>
                @endif
                @if ($invoice->adjustment_amount != 0)
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Penyesuaian</span><span class="font-medium text-gray-900 dark:text-white">{{ $invoice->adjustment_amount > 0 ? '+' : '' }}{{ \App\Support\Money::format($invoice->adjustment_amount) }}</span></div>
                @endif
                <div class="flex justify-between border-t border-gray-100 pt-3 text-sm dark:border-gray-700">
                    <span class="font-semibold text-gray-900 dark:text-white">Total</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ \App\Support\Money::format($invoice->total_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Dibayar</span><span class="text-gray-500">{{ \App\Support\Money::format($invoice->amount_paid) }}</span></div>
                <div class="flex justify-between text-sm">
                    <span class="font-semibold text-gray-900 dark:text-white">Sisa</span>
                    <span class="font-bold text-primary-600">{{ \App\Support\Money::format($invoice->amount_due) }}</span>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @if ($invoice->status->value === 'draft')
                    <x-ui.button wire:click="issue" class="w-full">Terbitkan Tagihan</x-ui.button>
                @endif

                @if (! $invoice->isVoid())
                    <form wire:submit="void" class="space-y-2">
                        <x-ui.input name="voidReason" label="Alasan Batal" wire:model="voidReason" placeholder="Mis. duplikasi, kesalahan nominal" />
                        <x-ui.button type="submit" variant="danger" class="w-full">Batalkan Tagihan</x-ui.button>
                    </form>
                @else
                    <p class="text-sm text-gray-500">Dibatalkan: {{ $invoice->void_reason }}</p>
                @endif
            </div>
        </x-ui.card>
    </div>

    <x-ui.card padding="p-0">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item</h3>
        </div>

        <x-ui.table :headings="['Jenis', 'Deskripsi', 'Qty × Harga', 'Jumlah', '']">
            @foreach ($invoice->items as $item)
                <tr>
                    <td class="px-4 py-3"><x-ui.badge>{{ $item->type->label() }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $item->description }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price) }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ \App\Support\Money::format($item->amount) }}</td>
                    <td class="px-4 py-3 text-right">
                        @can('invoice.update')
                            @unless ($invoice->isVoid())
                                <x-ui.confirm-dialog title="Hapus item" :message="'Hapus item '.$item->description.'?'" :action="'removeItem('.$item->id.')'">
                                    <x-slot:trigger>
                                        <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950">Hapus</button>
                                    </x-slot:trigger>
                                </x-ui.confirm-dialog>
                            @endunless
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        @can('invoice.update')
            @unless ($invoice->isVoid())
                <div class="border-t border-gray-100 p-4 dark:border-gray-700">
                    <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Tambah Item</h4>
                    <form wire:submit="addItem" class="grid gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-3">
                            <select wire:model="newItem.item" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-4">
                            <input type="text" wire:model="newItem.description" placeholder="Deskripsi" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="number" min="1" wire:model="newItem.quantity" class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="number" min="0" wire:model="newItem.unit_price" class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="sm:col-span-1">
                            <x-ui.button type="submit" size="sm" class="w-full">Tambah</x-ui.button>
                        </div>
                    </form>
                </div>
            @endunless
        @endcan
    </x-ui.card>
</div>
