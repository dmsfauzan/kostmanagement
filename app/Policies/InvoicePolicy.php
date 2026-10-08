<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasAnyRole(['owner', 'admin', 'finance'])) {
            return true;
        }

        return $invoice->tenant_id === $user->tenant?->id;
    }

    public function create(User $user): bool
    {
        return $user->can('invoice.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.update') && ! $invoice->isVoid();
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.void') && ! $invoice->isVoid();
    }
}
