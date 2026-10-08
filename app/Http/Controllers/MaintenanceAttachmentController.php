<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceAttachmentController extends Controller
{
    public function __invoke(MaintenanceAttachment $attachment): StreamedResponse
    {
        $user = request()->user();
        $ticket = $attachment->ticket;

        $owns = $ticket->tenant_id !== null && $ticket->tenant_id === $user->tenant?->id;
        $staff = $user->can('maintenance.view');

        abort_unless($owns || $staff, 404);

        $disk = Storage::disk(config('kost.disk.private'));
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, basename($attachment->path));
    }
}
