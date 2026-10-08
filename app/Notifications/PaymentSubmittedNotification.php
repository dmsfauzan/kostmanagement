<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Payment $payment) {}

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
            ->subject('Pembayaran Baru Menunggu Verifikasi')
            ->line('Pembayaran baru telah dikirim untuk tagihan '.$this->payment->invoice?->invoice_number.'.')
            ->line('Jumlah: '.Money::format($this->payment->amount))
            ->action('Tinjau Pembayaran', route('admin.payments.show', $this->payment));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pembayaran baru menunggu verifikasi untuk '.$this->payment->invoice?->invoice_number.' ('.Money::format($this->payment->amount).').',
            'payment_id' => $this->payment->id,
        ];
    }
}
