<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tagihan Jatuh Tempo '.$this->invoice->invoice_number)
            ->greeting('Halo '.($this->invoice->tenant?->full_name ?? '').'!')
            ->line('Tagihan Anda telah melewati tanggal jatuh tempo.')
            ->line('Nomor: '.$this->invoice->invoice_number)
            ->line('Sisa: '.Money::format($this->invoice->amount_due))
            ->action('Lihat Tagihan', route('tenant.invoices.show', $this->invoice))
            ->line('Mohon segera lakukan pembayaran.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Tagihan '.$this->invoice->invoice_number.' telah jatuh tempo.',
            'invoice_number' => $this->invoice->invoice_number,
            'amount_due' => $this->invoice->amount_due,
        ];
    }
}
