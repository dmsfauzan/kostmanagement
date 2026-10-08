<x-layouts.tenant title="Pembayaran">
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" action="{{ route('tenant.payments') }}" class="flex gap-2">
            <select name="status" class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua pembayaran</option>
                @foreach (\App\Enums\PaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <x-ui.button type="submit">Tampilkan</x-ui.button>
        </form>
    </div>

    @if ($payments->isEmpty())
        <x-ui.empty-state title="Belum ada pembayaran" description="Riwayat pembayaran Anda akan tampil di sini." />
    @else
        <div class="space-y-3">
            @foreach ($payments as $payment)
                <x-ui.card padding="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ \App\Support\Money::format($payment->amount) }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $payment->invoice?->invoice_number ?? '—' }} · {{ $payment->paid_at->format('d M Y') }} · {{ $payment->method?->name ?? '—' }}</p>
                            @if ($payment->rejection_reason)
                                <p class="mt-1 text-xs text-danger-600">Ditolak: {{ $payment->rejection_reason }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <x-ui.status-badge :status="$payment->status" />
                            @if ($payment->isPending())
                                <form method="POST" action="{{ route('tenant.payments.cancel', $payment) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-danger-600 hover:text-danger-700">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div>{{ $payments->links() }}</div>
    @endif
</x-layouts.tenant>
