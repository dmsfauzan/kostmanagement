<?php

namespace App\Notifications;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Lease $lease,
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
        return (new MailMessage)
            ->subject('Kontrak Akan Berakhir')
            ->line('Kontrak '.$this->lease->code.' akan berakhir dalam '.$this->daysBefore.' hari.')
            ->line('Berakhir: '.$this->lease->end_date?->translatedFormat('d F Y'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Kontrak '.$this->lease->code.' berakhir dalam '.$this->daysBefore.' hari.',
            'lease_code' => $this->lease->code,
        ];
    }
}
