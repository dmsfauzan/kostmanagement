<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\LateFeeType;
use App\Enums\LeaseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id', 'room_id', 'property_id', 'code', 'start_date', 'end_date',
    'rent_amount', 'deposit_amount', 'billing_cycle', 'due_day',
    'late_fee_type', 'late_fee_value', 'status', 'signed_at',
    'terminated_at', 'termination_reason', 'notes', 'created_by',
])]
class Lease extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_at' => 'datetime',
            'terminated_at' => 'datetime',
            'rent_amount' => 'integer',
            'deposit_amount' => 'integer',
            'late_fee_value' => 'integer',
            'due_day' => 'integer',
            'billing_cycle' => BillingCycle::class,
            'late_fee_type' => LateFeeType::class,
            'status' => LeaseStatus::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deposit(): HasOne
    {
        return $this->hasOne(Deposit::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LeaseStatusHistory::class)->latest();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LeaseStatus::Active->value,
            LeaseStatus::Expiring->value,
        ]);
    }

    public function scopeOfStatus(Builder $query, LeaseStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [LeaseStatus::Active, LeaseStatus::Expiring], true);
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false));
    }
}
