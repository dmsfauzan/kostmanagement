<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentVerifiedNotification extends Notification implements ShouldQueue
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
            ->subject('Pembayaran Terverifikasi')
            ->line('Pembayaran Anda untuk '.$this->payment->invoice?->invoice_number.' telah diverifikasi.')
            ->line('Jumlah: '.Money::format($this->payment->amount));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pembayaran untuk '.$this->payment->invoice?->invoice_number.' telah diverifikasi.',
            'payment_id' => $this->payment->id,
        ];
    }
}
