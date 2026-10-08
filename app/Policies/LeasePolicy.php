<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;

class LeasePolicy
{
    public function view(User $user, Lease $lease): bool
    {
        if ($user->hasAnyRole(['owner', 'admin', 'finance', 'technician'])) {
            return true;
        }

        return $lease->tenant_id === $user->tenant?->id;
    }

    public function create(User $user): bool
    {
        return $user->can('lease.create');
    }

    public function update(User $user, Lease $lease): bool
    {
        return $user->can('lease.update');
    }

    public function terminate(User $user, Lease $lease): bool
    {
        return $user->can('lease.terminate');
    }
}
