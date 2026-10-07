<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'property_id', 'building_id', 'floor_id', 'room_type_id', 'number', 'slug',
    'price', 'deposit', 'size_sqm', 'description', 'status',
])]
class Room extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'deposit' => 'integer',
            'status' => RoomStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Room $room): void {
            if (blank($room->slug)) {
                $base = Str::slug((string) $room->number) ?: 'room';
                $slug = $base;

                if (static::query()->where('slug', $slug)->whereKeyNot($room->getKey())->exists()) {
                    $slug = $base.'-'.($room->property_id ?? 'x');
                }

                $room->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RoomPhoto::class)->orderBy('sort_order');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenity');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Available->value);
    }

    public function scopeOfStatus(Builder $query, RoomStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where('number', 'like', "%{$term}%"));
    }

    public function isAvailable(): bool
    {
        return $this->status === RoomStatus::Available;
    }
}
