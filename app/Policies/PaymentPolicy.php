<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['owner', 'admin', 'finance'])) {
            return true;
        }

        return $payment->tenant_id === $user->tenant?->id;
    }

    public function create(User $user): bool
    {
        return $user->can('payment.create');
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $user->can('payment.verify') && $payment->isPending();
    }

    public function reject(User $user, Payment $payment): bool
    {
        return $user->can('payment.reject') && $payment->isPending();
    }
}
