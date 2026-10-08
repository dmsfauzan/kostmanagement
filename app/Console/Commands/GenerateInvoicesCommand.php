<?php

namespace App\Console\Commands;

use App\Jobs\GenerateInvoiceJob;
use App\Models\Invoice;
use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInvoicesCommand extends Command
{
    protected $signature = 'billing:generate-invoices
        {--period= : Billing period in Y-m format (default: current month)}
        {--lease= : Limit to a single lease id}';

    protected $description = 'Generate the recurring monthly invoice for every active lease';

    public function handle(): int
    {
        $periodKey = (string) ($this->option('period') ?: now()->format('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodKey)) {
            $this->error('Periode tidak valid. Gunakan format YYYY-MM.');

            return self::FAILURE;
        }

        $periodStart = Carbon::parse($periodKey.'-01');
        $periodEnd = $periodStart->copy()->endOfMonth()->startOfDay();

        $query = Lease::query()
            ->whereIn('status', ['active', 'expiring'])
            ->where('start_date', '<=', $periodEnd->toDateString())
            ->where('end_date', '>=', $periodStart->toDateString())
            ->when($this->option('lease'), fn ($q) => $q->whereKey($this->option('lease')));

        $count = 0;

        foreach ($query->lazyById(100) as $lease) {
            $invoice = Invoice::query()
                ->where('lease_id', $lease->id)
                ->where('billing_period', $periodKey)
                ->first();

            if ($invoice) {
                $this->line("Lewati lease #{$lease->id} ({$periodKey} sudah ada).");

                continue;
            }

            GenerateInvoiceJob::dispatchSync($lease->id, $periodKey);
            $count++;
        }

        $this->info("Berhasil membuat {$count} tagihan untuk periode {$periodKey}.");

        return self::SUCCESS;
    }
}
