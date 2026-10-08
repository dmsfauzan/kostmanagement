<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendLeaseExpiryRemindersCommand extends Command
{
    protected $signature = 'leases:send-expiry-reminders';

    protected $description = 'Notify tenants when their lease is about to expire';

    public function handle(ReminderService $reminders): int
    {
        $count = $reminders->sendLeaseExpiryReminders();

        $this->info("Mengirim {$count} pengingat berakhir kontrak.");

        return self::SUCCESS;
    }
}
