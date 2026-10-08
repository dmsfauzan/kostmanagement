<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\LateFeeCalculator;
use Illuminate\Console\Command;

class ApplyLateFeesCommand extends Command
{
    protected $signature = 'billing:apply-late-fees';

    protected $description = 'Recalculate deterministic late fees on overdue invoices';

    public function handle(BillingService $billing, LateFeeCalculator $calculator): int
    {
        $count = 0;

        Invoice::query()
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Overdue->value,
            ])
            ->whereDate('due_date', '<', now()->startOfDay()->toDateString())
            ->with('lease')
            ->chunkById(100, function ($invoices) use (&$count, $billing, $calculator): void {
                foreach ($invoices as $invoice) {
                    $fee = $calculator->calculate($invoice);
                    $billing->syncLateFee($invoice, $fee);
                    $count++;
                }
            });

        $this->info("Menerapkan denda pada {$count} tagihan.");

        return self::SUCCESS;
    }
}
