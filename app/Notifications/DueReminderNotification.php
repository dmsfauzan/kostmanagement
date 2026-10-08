<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly int $daysBefore,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $when = $this->daysBefore === 0 ? 'hari ini' : "dalam {$this->daysBefore} hari";

        return (new MailMessage)
            ->subject('Pengingat Tagihan '.$this->invoice->invoice_number)
            ->line('Tagihan '.$this->invoice->invoice_number.' jatuh tempo '.$when.'.')
            ->line('Sisa: '.Money::format($this->invoice->amount_due));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pengingat: tagihan '.$this->invoice->invoice_number.' jatuh tempo '.($this->daysBefore === 0 ? 'hari ini' : "H-{$this->daysBefore}").'.',
            'invoice_number' => $this->invoice->invoice_number,
        ];
    }
}
