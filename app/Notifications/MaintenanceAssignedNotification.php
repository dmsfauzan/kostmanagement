<?php

namespace App\Notifications;

use App\Models\MaintenanceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MaintenanceTicket $ticket) {}

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
            ->subject('Anda Ditugaskan: '.$this->ticket->ticket_number)
            ->line('Anda ditugaskan untuk keluhan: '.$this->ticket->title)
            ->action('Lihat Tiket', route('admin.maintenance.show', $this->ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Anda ditugaskan pada tiket '.$this->ticket->ticket_number.': '.$this->ticket->title,
            'ticket_id' => $this->ticket->id,
        ];
    }
}
