<?php

namespace App\Notifications;

use App\Models\MaintenanceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MaintenanceStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly MaintenanceTicket $ticket,
        public readonly string $fromStatus,
        public readonly string $toStatus,
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
            ->subject('Status Keluhan Berubah: '.$this->ticket->ticket_number)
            ->line('Status keluhan berubah: '.Str::headline($this->fromStatus).' → '.Str::headline($this->toStatus).'.')
            ->line('Judul: '.$this->ticket->title)
            ->action('Lihat Detail', route('tenant.maintenance.show', $this->ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Status '.$this->ticket->ticket_number.' berubah: '.Str::headline($this->fromStatus).' → '.Str::headline($this->toStatus).'.',
            'ticket_id' => $this->ticket->id,
            'from' => $this->fromStatus,
            'to' => $this->toStatus,
        ];
    }
}
