<?php

namespace App\Services;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Events\AnnouncementPublished;
use App\Models\Announcement;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnnouncementService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $attachment = null): Announcement
    {
        $data['created_by'] = auth()->id();
        $data['status'] = AnnouncementStatus::Draft;

        $announcement = Announcement::query()->create($data);

        if ($attachment) {
            $announcement->update([
                'attachment_path' => $attachment->store('announcements/'.$announcement->id, config('kost.disk.private')),
            ]);
        }

        $this->audit->record('announcement.created', $announcement, [], $announcement->only(['title', 'target_type']), 'Announcement');

        return $announcement->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Announcement $announcement, array $data, ?UploadedFile $attachment = null): Announcement
    {
        $before = $announcement->only(['title', 'target_type', 'status']);
        $announcement->update($data);

        if ($attachment) {
            $announcement->update([
                'attachment_path' => $attachment->store('announcements/'.$announcement->id, config('kost.disk.private')),
            ]);
        }

        $this->audit->record('announcement.updated', $announcement, $before, $announcement->only(['title', 'target_type', 'status']), 'Announcement');

        return $announcement->refresh();
    }

    public function publish(Announcement $announcement): Announcement
    {
        if ($announcement->status === AnnouncementStatus::Published) {
            throw new InvalidArgumentException('Pengumuman sudah terbit.');
        }

        $scheduledAt = $announcement->publish_at ?? now();

        $announcement->update([
            'status' => AnnouncementStatus::Published,
            'publish_at' => $scheduledAt,
        ]);

        if ($scheduledAt->lessThanOrEqualTo(now())) {
            $this->dispatch($announcement);
        }

        $this->audit->record('announcement.published', $announcement, ['status' => 'draft'], [
            'status' => 'published',
        ], 'Announcement');

        return $announcement->refresh();
    }

    /**
     * Materialize recipients and notify them for an announcement that is due.
     */
    public function dispatch(Announcement $announcement): void
    {
        $userIds = $this->resolveRecipientUserIds($announcement);

        DB::transaction(function () use ($announcement, $userIds): void {
            foreach ($userIds as $userId) {
                $announcement->recipients()->firstOrCreate(['user_id' => $userId]);
            }
        });

        if ($userIds !== []) {
            event(new AnnouncementPublished($announcement->load('recipients')));
        }
    }

    public function archive(Announcement $announcement): Announcement
    {
        $announcement->update(['status' => AnnouncementStatus::Archived]);

        $this->audit->record('announcement.archived', $announcement, [], ['status' => 'archived'], 'Announcement');

        return $announcement->refresh();
    }

    public function markRead(Announcement $announcement, User $user): void
    {
        $recipient = $announcement->recipients()->where('user_id', $user->id)->first();

        $recipient?->markRead();
    }

    /**
     * @return list<int>
     */
    public function resolveRecipientUserIds(Announcement $announcement): array
    {
        $userIds = match ($announcement->target_type) {
            AnnouncementTarget::All => User::query()->role('tenant')->pluck('id'),
            AnnouncementTarget::Tenant => Tenant::query()->whereKey($announcement->target_id)->whereNotNull('user_id')->pluck('user_id'),
            AnnouncementTarget::Property => Tenant::query()->where('property_id', $announcement->target_id)->whereNotNull('user_id')->pluck('user_id'),
            AnnouncementTarget::Building => $this->userIdsByRoom(fn ($q) => $q->where('building_id', $announcement->target_id)),
            AnnouncementTarget::Floor => $this->userIdsByRoom(fn ($q) => $q->where('floor_id', $announcement->target_id)),
            AnnouncementTarget::Room => $this->userIdsByRoom(fn ($q) => $q->where('room_id', $announcement->target_id)),
        };

        return $userIds->unique()->values()->all();
    }

    /**
     * @param  Closure(Builder): void  $roomConstraint
     * @return Collection<int, int>
     */
    private function userIdsByRoom(Closure $roomConstraint): Collection
    {
        return Tenant::query()
            ->whereNotNull('user_id')
            ->whereHas('leases', function ($query) use ($roomConstraint): void {
                $query->whereIn('status', ['active', 'expiring'])
                    ->whereHas('room', $roomConstraint);
            })
            ->pluck('user_id');
    }
}
