<?php

namespace App\Models;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_id', 'room_id', 'tenant_id', 'ticket_number', 'title', 'category',
    'description', 'priority', 'status', 'assigned_to', 'sla_due_at', 'sla_notified_at',
    'acknowledged_at', 'resolved_at', 'closed_at', 'resolution_notes', 'created_by',
])]
class MaintenanceTicket extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => MaintenanceCategory::class,
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            'sla_due_at' => 'datetime',
            'sla_notified_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MaintenanceComment::class, 'ticket_id')->oldest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceAttachment::class, 'ticket_id')->latest();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(MaintenanceStatusHistory::class, 'ticket_id')->latest();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            MaintenanceStatus::Resolved->value,
            MaintenanceStatus::Closed->value,
            MaintenanceStatus::Rejected->value,
        ]);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function isSlaOverdue(): bool
    {
        return $this->isOpen() && $this->sla_due_at !== null && $this->sla_due_at->isPast();
    }

    public function daysUntilSla(): ?int
    {
        if ($this->sla_due_at === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->sla_due_at->startOfDay(), false);
    }
}
