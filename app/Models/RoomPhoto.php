<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['room_id', 'path', 'sort_order'])]
class RoomPhoto extends Model
{
    use HasFactory;

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function url(): string
    {
        return Storage::disk(config('kost.disk.public'))->url($this->path);
    }
}
