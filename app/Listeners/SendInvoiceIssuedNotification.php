<?php

namespace App\Listeners;

use App\Events\InvoiceIssued;
use App\Notifications\InvoiceIssuedNotification;

class SendInvoiceIssuedNotification
{
    public function handle(InvoiceIssued $event): void
    {
        $user = $event->invoice->tenant?->user;

        if ($user) {
            $user->notify(new InvoiceIssuedNotification($event->invoice));
        }
    }
}
