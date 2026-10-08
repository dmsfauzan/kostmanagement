<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('PaymentSeeder skipped: not in local/testing environment.');

            return;
        }

        $invoice = Invoice::query()
            ->where('status', InvoiceStatus::Issued->value)
            ->first();

        if (! $invoice) {
            $this->command?->warn('PaymentSeeder skipped: no issued invoice.');

            return;
        }

        $tenant = Tenant::query()->find($invoice->tenant_id);

        if (! $tenant) {
            $this->command?->warn('PaymentSeeder skipped: no tenant for invoice.');

            return;
        }

        app(PaymentService::class)->submit(
            $invoice,
            [
                'amount' => min(500000, $invoice->amount_due),
                'paid_at' => now()->subDay()->toDateString(),
                'notes' => 'Data contoh (menunggu verifikasi).',
            ],
        );
    }
}
