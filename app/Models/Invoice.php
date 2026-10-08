<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'property_id', 'tenant_id', 'lease_id', 'invoice_number', 'billing_period',
    'period_start', 'period_end', 'issue_date', 'due_date',
    'subtotal', 'discount_amount', 'late_fee_amount', 'adjustment_amount',
    'total_amount', 'amount_paid', 'amount_due', 'status', 'issued_at', 'paid_at',
    'voided_at', 'void_reason', 'notes', 'created_by',
])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'issue_date' => 'date',
            'due_date' => 'date',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'late_fee_amount' => 'integer',
            'adjustment_amount' => 'integer',
            'total_amount' => 'integer',
            'amount_paid' => 'integer',
            'amount_due' => 'integer',
            'status' => InvoiceStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'invoice_number';
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeOfStatus(Builder $query, InvoiceStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::Issued->value,
            InvoiceStatus::PartiallyPaid->value,
            InvoiceStatus::Overdue->value,
        ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::Overdue->value);
    }

    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        return $query->where('billing_period', $period);
    }

    public function isPayable(): bool
    {
        return $this->status->isPayable();
    }

    public function isVoid(): bool
    {
        return $this->status === InvoiceStatus::Void;
    }

    public function isOverdue(): bool
    {
        return $this->isPayable() && $this->due_date->isPast();
    }

    public function daysUntilDue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false);
    }
}
