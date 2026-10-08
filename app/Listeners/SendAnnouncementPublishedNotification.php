<?php

namespace App\Listeners;

use App\Events\AnnouncementPublished;
use App\Notifications\AnnouncementPublishedNotification;

class SendAnnouncementPublishedNotification
{
    public function handle(AnnouncementPublished $event): void
    {
        $event->announcement->recipients()->with('user')->get()
            ->each(fn ($recipient) => $recipient->user?->notify(
                new AnnouncementPublishedNotification($event->announcement),
            ));
    }
}
