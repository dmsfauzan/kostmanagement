<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendDueRemindersCommand extends Command
{
    protected $signature = 'billing:send-due-reminders';

    protected $description = 'Send invoice due-date reminders to tenants';

    public function handle(ReminderService $reminders): int
    {
        $count = $reminders->sendDueReminders();

        $this->info("Mengirim {$count} pengingat jatuh tempo.");

        return self::SUCCESS;
    }
}
