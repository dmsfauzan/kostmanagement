<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class InvoiceIssuedNotification extends Notification implements ShouldQueue
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
        $invoice = $this->invoice;

        return (new MailMessage)
            ->subject('Tagihan Baru '.$invoice->invoice_number)
            ->greeting('Halo '.($invoice->tenant?->full_name ?? '').'!')
            ->line('Tagihan baru telah diterbitkan.')
            ->line('Nomor: '.$invoice->invoice_number)
            ->line('Jumlah: '.Number::currency($invoice->total_amount, 'IDR', 'id'))
            ->line('Jatuh tempo: '.$invoice->due_date->translatedFormat('d F Y'))
            ->action('Lihat Tagihan', route('tenant.invoices.show', $invoice))
            ->line('Terima kasih.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Tagihan baru '.$this->invoice->invoice_number.' sebesar '.Money::format($this->invoice->total_amount),
            'invoice_number' => $this->invoice->invoice_number,
            'total_amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date->toDateString(),
        ];
    }
}
