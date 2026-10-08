<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Notifications\PaymentVerifiedNotification;

class SendPaymentVerifiedNotification
{
    public function handle(PaymentVerified $event): void
    {
        $user = $event->payment->tenant?->user;

        if ($user) {
            $user->notify(new PaymentVerifiedNotification($event->payment));
        }
    }
}
