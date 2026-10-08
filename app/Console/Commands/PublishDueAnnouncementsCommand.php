<?php

namespace App\Console\Commands;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Console\Command;

class PublishDueAnnouncementsCommand extends Command
{
    protected $signature = 'announcements:publish-due';

    protected $description = 'Publish scheduled announcements whose publish_at has passed';

    public function handle(AnnouncementService $announcements): int
    {
        $count = 0;

        Announcement::query()
            ->where('status', AnnouncementStatus::Published->value)
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->whereDoesntHave('recipients')
            ->chunkById(100, function ($items) use (&$count, $announcements): void {
                foreach ($items as $announcement) {
                    $announcements->dispatch($announcement);
                    $count++;
                }
            });

        $this->info("Menerbitkan {$count} pengumuman terjadwal.");

        return self::SUCCESS;
    }
}
