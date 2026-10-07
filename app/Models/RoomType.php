<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['property_id', 'name', 'slug', 'default_price', 'default_deposit', 'size_sqm', 'capacity', 'description'])]
class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (RoomType $type): void {
            if (blank($type->slug)) {
                $type->slug = Str::slug((string) $type->name);
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_type_amenity');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function scopeAvailableRooms(Builder $query): Builder
    {
        return $query->whereHas('rooms', fn (Builder $rooms) => $rooms->available());
    }
}
