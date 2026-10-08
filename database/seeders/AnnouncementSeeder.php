<?php

namespace Database\Seeders;

use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        if (Announcement::query()->exists()) {
            return;
        }

        $service = app(AnnouncementService::class);

        $announcement = $service->create([
            'title' => 'Pemeliharaan Air Minggu Ini',
            'body' => "Akan dilakukan pemeliharaan instalasi air pada hari Sabtu pukul 09.00–12.00.\nMohon maaf atas ketidaknyamanannya.",
            'target_type' => AnnouncementTarget::All,
        ]);

        $service->publish($announcement);

        $this->command?->info('AnnouncementSeeder: announcement published.');
    }
}
