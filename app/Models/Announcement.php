<?php

namespace App\Models;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_id', 'title', 'body', 'target_type', 'target_id',
    'publish_at', 'expires_at', 'attachment_path', 'status', 'created_by',
])]
class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_type' => AnnouncementTarget::class,
            'status' => AnnouncementStatus::class,
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AnnouncementRecipient::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', AnnouncementStatus::Published->value);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()
            ->where(fn (Builder $q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('recipients', fn (Builder $q) => $q->where('user_id', $userId));
    }

    public function isPublished(): bool
    {
        return $this->status === AnnouncementStatus::Published;
    }

    public function isVisible(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        if ($this->publish_at && $this->publish_at->isFuture()) {
            return false;
        }

        return ! ($this->expires_at && $this->expires_at->isPast());
    }
}
