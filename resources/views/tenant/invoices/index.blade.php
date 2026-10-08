<x-layouts.tenant title="Tagihan">
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" action="{{ route('tenant.invoices') }}" class="flex gap-2">
            <select name="status" class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua tagihan</option>
                @foreach (\App\Enums\InvoiceStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <x-ui.button type="submit">Tampilkan</x-ui.button>
        </form>
    </div>

    @if ($invoices->isEmpty())
        <x-ui.empty-state title="Belum ada tagihan" description="Tagihan Anda akan tampil di sini." />
    @else
        <div class="space-y-3">
            @foreach ($invoices as $invoice)
                <a href="{{ route('tenant.invoices.show', $invoice) }}" class="block rounded-xl border border-gray-200 bg-white p-4 transition hover:shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                @if ($invoice->billing_period)
                                    Periode {{ $invoice->billing_period }}
                                @elseif ($invoice->period_start)
                                    {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end?->format('d M Y') }}
                                @endif
                                · Jt. tempo {{ $invoice->due_date->format('d M Y') }}
                            </p>
                        </div>
                        <x-ui.status-badge :status="$invoice->status" />
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                        <span class="text-xs text-gray-500">{{ $invoice->lease?->room?->number ?? '—' }}</span>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ \App\Support\Money::format($invoice->total_amount) }}</p>
                            <p class="text-xs text-gray-500">Sisa {{ \App\Support\Money::format($invoice->amount_due) }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div>{{ $invoices->links() }}</div>
    @endif
</x-layouts.tenant>
