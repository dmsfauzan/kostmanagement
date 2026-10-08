<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly int $daysOverdue,
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
        return (new MailMessage)
            ->subject('Tagihan Terlambat '.$this->invoice->invoice_number)
            ->line('Tagihan '.$this->invoice->invoice_number.' telah terlambat '.$this->daysOverdue.' hari.')
            ->line('Sisa: '.Money::format($this->invoice->amount_due));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Tagihan '.$this->invoice->invoice_number.' terlambat '.$this->daysOverdue.' hari.',
            'invoice_number' => $this->invoice->invoice_number,
        ];
    }
}
