<?php

namespace App\Console\Commands;

use App\Events\MaintenanceSlaBreached;
use App\Models\MaintenanceTicket;
use Illuminate\Console\Command;

class CheckMaintenanceSlaCommand extends Command
{
    protected $signature = 'maintenance:check-sla';

    protected $description = 'Notify staff when maintenance tickets exceed their SLA';

    public function handle(): int
    {
        $count = 0;

        MaintenanceTicket::query()
            ->open()
            ->whereNotNull('sla_due_at')
            ->whereNull('sla_notified_at')
            ->where('sla_due_at', '<', now())
            ->chunkById(100, function ($tickets) use (&$count): void {
                foreach ($tickets as $ticket) {
                    $ticket->update(['sla_notified_at' => now()]);

                    event(new MaintenanceSlaBreached($ticket));

                    $count++;
                }
            });

        $this->info("Memeriksa SLA: {$count} tiket melebihi SLA.");

        return self::SUCCESS;
    }
}
