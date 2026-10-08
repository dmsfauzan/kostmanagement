<?php

namespace Database\Seeders;

use App\Enums\InvoiceItemType;
use App\Models\Lease;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('BillingSeeder skipped: not in local/testing environment.');

            return;
        }

        $leases = Lease::query()
            ->whereIn('status', ['active', 'expiring'])
            ->with('tenant')
            ->get();

        if ($leases->isEmpty()) {
            $this->command?->warn('BillingSeeder skipped: no active lease.');

            return;
        }

        $billing = app(BillingService::class);

        foreach ($leases as $lease) {
            $periodStart = Carbon::now()->firstOfMonth();
            $periodEnd = Carbon::now()->endOfMonth()->startOfDay();

            try {
                $invoice = $billing->createForLease(
                    $lease,
                    $periodStart,
                    $periodEnd,
                    baseItems: [[
                        'type' => InvoiceItemType::Rent,
                        'description' => 'Sewa '.$periodStart->translatedFormat('F Y'),
                        'quantity' => 1,
                        'unit_price' => $lease->rent_amount,
                    ]],
                );

                $billing->issue($invoice);
            } catch (\InvalidArgumentException) {
                continue;
            }
        }
    }
}
