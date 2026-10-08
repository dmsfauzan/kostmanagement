<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        if ($user->hasAnyRole(['owner', 'admin', 'finance'])) {
            return true;
        }

        return $user->tenant?->is($tenant) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->can('tenant.create');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->can('tenant.update');
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->can('tenant.update');
    }
}
