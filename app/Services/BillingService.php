<?php

namespace App\Services;

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Events\InvoiceIssued;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillingService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly ProrationCalculator $proration,
        private readonly InvoiceNumberGenerator $numbers,
        private readonly SettingsService $settings,
    ) {}

    /**
     * @param  list<array{type: InvoiceItemType, description: string, quantity: int, unit_price: int}>  $baseItems
     */
    public function createForLease(
        Lease $lease,
        Carbon $periodStart,
        Carbon $periodEnd,
        array $baseItems = [],
        ?string $notes = null,
    ): Invoice {
        $periodKey = $periodStart->format('Y-m');

        if (Invoice::query()->where('lease_id', $lease->id)->where('billing_period', $periodKey)->exists()) {
            throw new InvalidArgumentException('Tagihan untuk periode '.$periodKey.' sudah ada.');
        }

        $tenant = Tenant::query()->findOrFail($lease->tenant_id);

        if ($baseItems === []) {
            $rent = $this->proration->rent($lease->rent_amount, $periodStart, $periodEnd, $lease->start_date);
            $baseItems = [[
                'type' => InvoiceItemType::Rent,
                'description' => 'Sewa '.$periodStart->translatedFormat('F Y'),
                'quantity' => 1,
                'unit_price' => $rent,
            ]];
        }

        return $this->persist(
            lease: $lease,
            tenant: $tenant,
            periodKey: $periodKey,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            baseItems: $baseItems,
            notes: $notes,
        );
    }

    /**
     * @param  list<array{type: InvoiceItemType, description: string, quantity: int, unit_price: int}>  $items
     */
    public function createManual(
        Lease $lease,
        array $items,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        ?string $notes = null,
    ): Invoice {
        $tenant = Tenant::query()->findOrFail($lease->tenant_id);

        return $this->persist(
            lease: $lease,
            tenant: $tenant,
            periodKey: null,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            baseItems: $items,
            notes: $notes,
        );
    }

    public function recalculate(Invoice $invoice): Invoice
    {
        $items = $invoice->items;
        $subtotal = 0;
        $discount = 0;
        $lateFee = 0;
        $adjustment = 0;

        foreach ($items as $item) {
            $amount = (int) ($item->quantity * $item->unit_price);

            if ($amount !== (int) $item->amount) {
                $item->update(['amount' => $amount]);
            }

            match ($item->type) {
                InvoiceItemType::Discount => $discount += $amount,
                InvoiceItemType::LateFee => $lateFee += $amount,
                InvoiceItemType::Adjustment => $adjustment += (int) ($item->meta['signed_amount'] ?? $amount),
                default => $subtotal += $amount,
            };
        }

        $total = max(0, $subtotal - $discount + $lateFee + $adjustment);
        $amountPaid = (int) $invoice->amount_paid;
        $amountDue = max(0, $total - $amountPaid);

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'late_fee_amount' => $lateFee,
            'adjustment_amount' => $adjustment,
            'total_amount' => $total,
            'amount_due' => $amountDue,
        ]);

        return $invoice;
    }

    /**
     * @param  array{item: InvoiceItemType|string, description: string, quantity: int, unit_price: int, signed_amount?: int}  $item
     */
    public function addItem(Invoice $invoice, array $item): Invoice
    {
        if ($invoice->isVoid()) {
            throw new InvalidArgumentException('Invoice sudah dibatalkan.');
        }

        $type = $item['type'] instanceof InvoiceItemType ? $item['type'] : InvoiceItemType::from((string) $item['type']);
        $quantity = (int) ($item['quantity'] ?? 1);
        $unitPrice = (int) ($item['unit_price'] ?? 0);
        $amount = $quantity * $unitPrice;

        $meta = null;

        if ($type === InvoiceItemType::Adjustment) {
            $meta = ['signed_amount' => (int) ($item['signed_amount'] ?? $amount)];
        }

        $invoice->items()->create([
            'type' => $type,
            'description' => $item['description'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'meta' => $meta,
            'sort_order' => (int) $invoice->items()->max('sort_order') + 1,
        ]);

        return $this->recalculate($invoice->refresh());
    }

    public function removeItem(Invoice $invoice, int $itemId): Invoice
    {
        if ($invoice->isVoid()) {
            throw new InvalidArgumentException('Invoice sudah dibatalkan.');
        }

        $invoice->items()->findOrFail($itemId)->delete();

        return $this->recalculate($invoice->refresh());
    }

    /**
     * Replace (or clear) the late-fee line item with a deterministic amount.
     */
    public function syncLateFee(Invoice $invoice, int $amount): Invoice
    {
        $item = $invoice->items()->where('type', InvoiceItemType::LateFee->value)->first();

        if ($amount <= 0) {
            $item?->delete();
        } elseif ($item) {
            $item->update(['quantity' => 1, 'unit_price' => $amount, 'amount' => $amount]);
        } else {
            $invoice->items()->create([
                'type' => InvoiceItemType::LateFee,
                'description' => 'Denda keterlambatan',
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
                'sort_order' => (int) $invoice->items()->max('sort_order') + 1,
            ]);
        }

        return $this->recalculate($invoice->refresh());
    }

    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new InvalidArgumentException('Invoice hanya dapat diterbitkan dari status draft.');
        }

        $invoice->update([
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
        ]);

        $this->audit->record('invoice.issued', $invoice, ['status' => 'draft'], ['status' => 'issued'], 'Invoice');

        event(new InvoiceIssued($invoice->load(['tenant', 'lease'])));

        return $invoice;
    }

    public function void(Invoice $invoice, string $reason): Invoice
    {
        if ($invoice->isVoid()) {
            throw new InvalidArgumentException('Invoice sudah dibatalkan.');
        }

        $before = $invoice->only(['status']);
        $invoice->update([
            'status' => InvoiceStatus::Void,
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);

        $this->audit->record('invoice.voided', $invoice, $before, [
            'status' => 'void',
            'reason' => $reason,
        ], 'Invoice');

        return $invoice;
    }

    /**
     * @param  list<array{type: InvoiceItemType, description: string, quantity: int, unit_price: int}>  $baseItems
     */
    private function persist(
        Lease $lease,
        Tenant $tenant,
        ?string $periodKey,
        ?Carbon $periodStart,
        ?Carbon $periodEnd,
        array $baseItems,
        ?string $notes,
    ): Invoice {
        $dueDays = (int) $this->settings->get('billing.due_days', 10);
        $issueDate = now()->toDateString();
        $dueDate = now()->copy()->addDays($dueDays)->toDateString();

        $invoice = DB::transaction(function () use ($lease, $tenant, $periodKey, $periodStart, $periodEnd, $baseItems, $issueDate, $dueDate, $notes): Invoice {
            $invoice = Invoice::query()->create([
                'property_id' => $lease->property_id,
                'tenant_id' => $tenant->id,
                'lease_id' => $lease->id,
                'invoice_number' => $this->numbers->generate(),
                'billing_period' => $periodKey,
                'period_start' => $periodStart?->toDateString(),
                'period_end' => $periodEnd?->toDateString(),
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => InvoiceStatus::Draft,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($baseItems as $index => $item) {
                $invoice->items()->create([
                    'type' => $item['type'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'],
                    'amount' => ($item['quantity'] ?? 1) * $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            return $invoice;
        });

        $this->recalculate($invoice);

        $this->audit->record('invoice.created', $invoice, [], $invoice->only([
            'invoice_number', 'total_amount', 'status',
        ]), 'Invoice');

        return $invoice->refresh();
    }
}
