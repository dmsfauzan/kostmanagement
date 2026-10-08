<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lease_id', 'from_status', 'to_status', 'reason', 'actor_id'])]
class LeaseStatusHistory extends Model
{
    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
