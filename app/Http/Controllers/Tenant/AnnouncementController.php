<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $announcements = Announcement::query()
            ->visible()
            ->forUser($request->user()->id)
            ->with(['creator', 'recipients'])
            ->latest()
            ->paginate(10);

        return view('tenant.announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    public function show(Request $request, Announcement $announcement): View
    {
        abort_unless(
            $announcement->isVisible() && $announcement->recipients()->where('user_id', $request->user()->id)->exists(),
            404,
        );

        app(AnnouncementService::class)->markRead($announcement, $request->user());

        return view('tenant.announcements.show', [
            'announcement' => $announcement->load('creator'),
        ]);
    }
}
