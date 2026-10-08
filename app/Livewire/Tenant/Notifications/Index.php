<?php

namespace App\Livewire\Tenant\Notifications;

use App\Models\Announcement;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function markRead(string $id): void
    {
        $notification = auth()->user()->notifications()->whereKey($id)->first();
        $notification?->markAsRead();

        if ($notification && isset($notification->data['announcement_id'])) {
            $announcement = Announcement::query()->find($notification->data['announcement_id']);

            if ($announcement) {
                $this->redirectRoute('tenant.announcements.show', $announcement, navigate: true);

                return;
            }
        }
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        return view('livewire.tenant.notifications.index', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(20),
        ])->layout('components.layouts.tenant', ['title' => 'Notifikasi']);
    }
}
