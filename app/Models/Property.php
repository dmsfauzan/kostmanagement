<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'owner_id', 'name', 'slug', 'address', 'city', 'province', 'postal_code',
    'phone', 'email', 'description', 'logo', 'cover', 'status',
])]
class Property extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PropertyStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property): void {
            if (blank($property->slug)) {
                $property->slug = Str::slug((string) $property->name).'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class)->through('buildings');
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Active->value);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk(config('kost.disk.public'))->url($this->logo) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover ? Storage::disk(config('kost.disk.public'))->url($this->cover) : null;
    }
}
