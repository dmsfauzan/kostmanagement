<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lease_id', 'tenant_id', 'amount', 'status', 'held_at', 'returned_at',
    'deduction_amount', 'refund_amount', 'notes',
])]
class Deposit extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'deduction_amount' => 'integer',
            'refund_amount' => 'integer',
            'held_at' => 'datetime',
            'returned_at' => 'datetime',
            'status' => DepositStatus::class,
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outstanding(): int
    {
        return max(0, $this->amount - $this->deduction_amount - $this->refund_amount);
    }
}
