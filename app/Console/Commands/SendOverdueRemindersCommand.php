<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendOverdueRemindersCommand extends Command
{
    protected $signature = 'billing:send-overdue-reminders';

    protected $description = 'Send overdue reminders for unpaid invoices';

    public function handle(ReminderService $reminders): int
    {
        $count = $reminders->sendOverdueReminders();

        $this->info("Mengirim {$count} pengingat keterlambatan.");

        return self::SUCCESS;
    }
}
