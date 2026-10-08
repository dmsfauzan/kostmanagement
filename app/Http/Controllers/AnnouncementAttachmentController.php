<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementAttachmentController extends Controller
{
    public function __invoke(Announcement $announcement): StreamedResponse
    {
        $user = request()->user();

        $isRecipient = $announcement->recipients()->where('user_id', $user->id)->exists();
        $staff = $user->isStaff();

        abort_unless($isRecipient || $staff, 404);
        abort_unless($announcement->attachment_path, 404);

        $disk = Storage::disk(config('kost.disk.private'));
        abort_unless($disk->exists($announcement->attachment_path), 404);

        return $disk->download($announcement->attachment_path, basename($announcement->attachment_path));
    }
}
