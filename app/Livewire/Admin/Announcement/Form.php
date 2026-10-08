<?php

namespace App\Livewire\Admin\Announcement;

use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Services\AnnouncementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Announcement $announcement = null;

    public string $title = '';

    public string $body = '';

    public string $target_type = 'all';

    public ?int $target_id = null;

    public ?string $publish_at = '';

    public ?string $expires_at = '';

    public $attachment;

    public function mount(?Announcement $announcement = null): void
    {
        if ($announcement && $announcement->exists) {
            Gate::authorize('announcement.update');

            $this->announcement = $announcement;
            $this->title = $announcement->title;
            $this->body = $announcement->body;
            $this->target_type = $announcement->target_type->value;
            $this->target_id = $announcement->target_id;
            $this->publish_at = $announcement->publish_at?->format('Y-m-d\TH:i');
            $this->expires_at = $announcement->expires_at?->format('Y-m-d\TH:i');
        } else {
            Gate::authorize('announcement.create');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
            'target_type' => ['required', Rule::enum(AnnouncementTarget::class)],
            'target_id' => ['nullable', 'integer'],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:publish_at'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $attachment = $this->attachment;
        unset($data['attachment']);

        $data['publish_at'] = $data['publish_at'] ? Carbon::parse($data['publish_at']) : null;
        $data['expires_at'] = $data['expires_at'] ? Carbon::parse($data['expires_at']) : null;
        $data['target_id'] = $data['target_id'] ?: null;

        $service = app(AnnouncementService::class);

        if ($this->announcement) {
            $announcement = $service->update($this->announcement, $data, $attachment);
            session()->flash('status', 'Pengumuman diperbarui.');

            $this->redirectRoute('admin.announcements', navigate: true);
        } else {
            $announcement = $service->create($data, $attachment);
            session()->flash('status', 'Pengumuman dibuat (status draft).');

            $this->redirectRoute('admin.announcements', navigate: true);
        }
    }

    public function render()
    {
        $targets = $this->resolveTargetOptions();

        return view('livewire.admin.announcement.form', [
            'announcement' => $this->announcement,
            'targets' => AnnouncementTarget::cases(),
            'targetOptions' => $targets,
        ])->layout('components.layouts.admin', [
            'title' => $this->announcement ? 'Ubah Pengumuman' : 'Tambah Pengumuman',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function resolveTargetOptions(): array
    {
        return match ($this->target_type) {
            AnnouncementTarget::Property->value => Property::query()->pluck('name', 'id')->all(),
            AnnouncementTarget::Building->value => Building::query()->pluck('name', 'id')->all(),
            AnnouncementTarget::Floor->value => Floor::query()->pluck('name', 'id')->all(),
            AnnouncementTarget::Room->value => Room::query()->pluck('number', 'id')->all(),
            AnnouncementTarget::Tenant->value => Tenant::query()->pluck('full_name', 'id')->all(),
            default => [],
        };
    }
}
