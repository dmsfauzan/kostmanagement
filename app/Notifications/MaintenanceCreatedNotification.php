<?php

namespace App\Notifications;

use App\Models\MaintenanceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceCreatedNotification extends Notification implements ShouldQueue
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
            ->subject('Keluhan Baru '.$this->ticket->ticket_number)
            ->line('Keluhan baru diajukan: '.$this->ticket->title)
            ->line('Kategori: '.$this->ticket->category->label().' · Prioritas: '.$this->ticket->priority->label())
            ->action('Tinjau Tiket', route('admin.maintenance.show', $this->ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Keluhan baru '.$this->ticket->ticket_number.': '.$this->ticket->title,
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
        ];
    }
}
