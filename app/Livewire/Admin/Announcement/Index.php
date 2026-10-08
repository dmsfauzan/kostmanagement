<?php

namespace App\Livewire\Admin\Announcement;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function publish(int $id): void
    {
        Gate::authorize('announcement.update');

        $announcement = Announcement::query()->findOrFail($id);

        try {
            app(AnnouncementService::class)->publish($announcement);
            session()->flash('status', 'Pengumuman diterbitkan.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function archive(int $id): void
    {
        Gate::authorize('announcement.update');

        app(AnnouncementService::class)->archive(Announcement::query()->findOrFail($id));
        session()->flash('status', 'Pengumuman diarsipkan.');
    }

    public function render()
    {
        Gate::authorize('announcement.view');

        $announcements = Announcement::query()
            ->with(['creator', 'property'])
            ->withCount('recipients')
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.announcement.index', [
            'announcements' => $announcements,
            'statuses' => AnnouncementStatus::cases(),
        ])->layout('components.layouts.admin', ['title' => 'Pengumuman']);
    }
}
