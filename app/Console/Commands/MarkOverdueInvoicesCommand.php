<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceOverdue;
use App\Models\Invoice;
use App\Services\AuditService;
use Illuminate\Console\Command;

class MarkOverdueInvoicesCommand extends Command
{
    protected $signature = 'billing:mark-overdue';

    protected $description = 'Mark unpaid invoices past their due date as overdue';

    public function handle(AuditService $audit): int
    {
        $count = 0;

        Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value])
            ->whereDate('due_date', '<', now()->startOfDay()->toDateString())
            ->with('tenant')
            ->chunkById(100, function ($invoices) use (&$count, $audit): void {
                foreach ($invoices as $invoice) {
                    $from = $invoice->status->value;

                    $invoice->update(['status' => InvoiceStatus::Overdue]);

                    $audit->record('invoice.overdue', $invoice, ['status' => $from], ['status' => 'overdue'], 'Invoice');

                    event(new InvoiceOverdue($invoice->load('tenant')));

                    $count++;
                }
            });

        $this->info("Menandai {$count} tagihan sebagai jatuh tempo.");

        return self::SUCCESS;
    }
}
