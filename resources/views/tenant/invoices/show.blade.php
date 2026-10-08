<x-layouts.tenant title="Detail Tagihan">
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ $invoice->lease?->room?->number }} · {{ $invoice->lease?->room?->property?->name }}</p>
            </div>
            <x-ui.status-badge :status="$invoice->status" />
        </div>

        <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm dark:border-gray-700">
            @foreach ($invoice->items as $item)
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-300">{{ $item->description }} <span class="text-xs text-gray-400">×{{ $item->quantity }}</span></span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ \App\Support\Money::format($item->amount) }}</span>
                </div>
            @endforeach
            @if ($invoice->discount_amount > 0)
                <div class="flex justify-between text-success-600">
                    <span>Diskon</span>
                    <span>-{{ \App\Support\Money::format($invoice->discount_amount) }}</span>
                </div>
            @endif
            @if ($invoice->late_fee_amount > 0)
                <div class="flex justify-between text-warning-600">
                    <span>Denda</span>
                    <span>+{{ \App\Support\Money::format($invoice->late_fee_amount) }}</span>
                </div>
            @endif
            @if ($invoice->adjustment_amount != 0)
                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                    <span>Penyesuaian</span>
                    <span>{{ \App\Support\Money::format($invoice->adjustment_amount) }}</span>
                </div>
            @endif
        </dl>

        <div class="mt-4 space-y-2 border-t border-gray-100 pt-4 dark:border-gray-700">
            <div class="flex justify-between text-sm">
                <span class="font-semibold text-gray-900 dark:text-white">Total</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ \App\Support\Money::format($invoice->total_amount) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="font-semibold text-gray-900 dark:text-white">Sisa Dibayar</span>
                <span class="font-bold text-primary-600">{{ \App\Support\Money::format($invoice->amount_due) }}</span>
            </div>
            <p class="text-xs text-gray-500">Jatuh tempo {{ $invoice->due_date->translatedFormat('d F Y') }}</p>
            @if ($invoice->notes)
                <p class="text-xs text-gray-500">{{ $invoice->notes }}</p>
            @endif
        </div>

        @if ($invoice->isPayable() && \Illuminate\Support\Facades\Route::has('tenant.payments'))
            <x-ui.button :href="route('tenant.payments')" variant="secondary" class="mt-5 w-full">Bayar Tagihan</x-ui.button>
        @endif
    </div>
</x-layouts.tenant>
