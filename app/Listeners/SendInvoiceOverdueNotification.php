<?php

namespace App\Listeners;

use App\Events\InvoiceOverdue;
use App\Notifications\InvoiceOverdueNotification;

class SendInvoiceOverdueNotification
{
    public function handle(InvoiceOverdue $event): void
    {
        $user = $event->invoice->tenant?->user;

        if ($user) {
            $user->notify(new InvoiceOverdueNotification($event->invoice));
        }
    }
}
