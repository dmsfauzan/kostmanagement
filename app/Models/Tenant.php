<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\LeaseStatus;
use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_id', 'user_id', 'full_name', 'nik', 'gender', 'birth_date', 'phone', 'email',
    'address', 'emergency_name', 'emergency_relationship', 'emergency_phone',
    'vehicle_type', 'vehicle_number', 'notes', 'status',
])]
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
            'status' => TenantStatus::class,
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenantDocument::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)
            ->whereIn('status', [LeaseStatus::Active->value, LeaseStatus::Expiring->value])
            ->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', TenantStatus::Active->value);
    }

    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }

    public function isActive(): bool
    {
        return $this->status === TenantStatus::Active;
    }
}
