<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class TenantInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Tenant $tenant) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tenant = $this->tenant;
        $user = $notifiable;

        $url = URL::temporarySignedRoute('tenant.activate', now()->addDays(7), [
            'user' => $user->id,
        ]);

        return (new MailMessage)
            ->subject('Undangan Akun — Kost Management')
            ->greeting('Hai '.$tenant->full_name.'!')
            ->line('Pengelola kost telah membuat akun penghuni untuk Anda.')
            ->line('Silakan atur kata sandi akun Anda dalam 7 hari ke depan.')
            ->action('Atur Kata Sandi', $url)
            ->line('Jika Anda tidak merasa mendaftar, abaikan email ini.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $user = $notifiable;
        $url = URL::temporarySignedRoute('tenant.activate', now()->addDays(7), [
            'user' => $user->id,
        ]);

        return [
            'full_name' => $this->tenant->full_name,
            'email' => $this->tenant->email,
            'activation_url' => $url,
        ];
    }
}
