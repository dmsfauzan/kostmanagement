<?php

namespace App\Livewire\Admin\Notifications;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function markRead(string $id): void
    {
        Gate::authorize('dashboard.view');

        auth()->user()->notifications()->whereKey($id)->first()?->markAsRead();
    }

    public function markAllRead(): void
    {
        Gate::authorize('dashboard.view');

        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        Gate::authorize('dashboard.view');

        return view('livewire.admin.notifications.index', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(20),
        ])->layout('components.layouts.admin', ['title' => 'Notifikasi']);
    }
}
