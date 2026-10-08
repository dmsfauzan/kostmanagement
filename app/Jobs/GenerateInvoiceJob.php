<?php

namespace App\Jobs;

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Models\Lease;
use App\Services\BillingService;
use App\Services\ProrationCalculator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $leaseId,
        public readonly string $periodKey,
    ) {}

    public function handle(BillingService $billing, ProrationCalculator $proration): void
    {
        $lease = Lease::query()->with('room')->find($this->leaseId);

        if (! $lease || ! in_array($lease->status->value, ['active', 'expiring'], true)) {
            return;
        }

        $periodStart = Carbon::parse($this->periodKey.'-01');
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();

        $rent = $proration->rent($lease->rent_amount, $periodStart, $periodEnd, $lease->start_date);

        try {
            $invoice = $billing->createForLease(
                $lease->refresh(),
                $periodStart,
                $periodEnd,
                baseItems: [[
                    'type' => InvoiceItemType::Rent,
                    'description' => 'Sewa '.$periodStart->translatedFormat('F Y'),
                    'quantity' => 1,
                    'unit_price' => $rent,
                ]],
            );
        } catch (\InvalidArgumentException) {
            return;
        }

        if ($invoice->status === InvoiceStatus::Draft) {
            $billing->issue($invoice);
        }
    }
}
