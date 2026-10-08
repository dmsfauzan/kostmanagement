<?php

namespace App\Services;

use App\Enums\TenantStatus;
use App\Enums\UserStatus;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tenant
    {
        $skipInvite = ! empty($data['skip_invite']);
        $data = $this->normalize($data);

        $tenant = Tenant::query()->create(array_merge($data, [
            'status' => TenantStatus::Prospect,
        ]));

        $this->audit->record('tenant.created', $tenant, [], $tenant->only([
            'full_name', 'email', 'phone',
        ]), 'Tenant');

        if (! empty($data['email']) && ! $skipInvite) {
            $this->invite($tenant);
        }

        return $tenant;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $data = $this->normalize($data);

        $before = $tenant->only(['full_name', 'phone', 'email', 'status']);
        $tenant->update($data);

        $this->audit->record('tenant.updated', $tenant, $before, $tenant->only([
            'full_name', 'phone', 'email', 'status',
        ]), 'Tenant');

        return $tenant;
    }

    public function invite(Tenant $tenant): Tenant
    {
        $tenant->refresh();

        if (blank($tenant->email)) {
            return $tenant;
        }

        if ($tenant->user_id) {
            $user = $tenant->user;

            if ($user && $user->status === UserStatus::Active) {
                return $tenant;
            }

            if ($user) {
                $user->notify(new TenantInvitationNotification($tenant));

                return $tenant;
            }
        }

        $user = User::query()->firstOrCreate(
            ['email' => $tenant->email],
            [
                'name' => $tenant->full_name,
                'phone' => $tenant->phone,
                'password' => Str::random(64),
                'status' => UserStatus::Inactive,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles(['tenant']);
        $user->update([
            'name' => $tenant->full_name,
            'phone' => $tenant->phone ?? $user->phone,
        ]);

        $tenant->update([
            'user_id' => $user->id,
            'status' => TenantStatus::Invited,
        ]);

        $user->notify(new TenantInvitationNotification($tenant));

        $this->audit->record('tenant.invited', $tenant, [], [
            'email' => $tenant->email,
            'user_id' => $user->id,
        ], 'Tenant');

        return $tenant;
    }

    public function destroy(Tenant $tenant): void
    {
        $before = $tenant->only(['full_name', 'status']);
        $this->audit->record('tenant.deleted', $tenant, $before, [], 'Tenant');

        $tenant->delete();
    }

    public function activate(Tenant $tenant, string $password, ?string $name = null): User
    {
        $user = $tenant->user()->firstOrFail();

        $user->update([
            'name' => $name ?: $tenant->full_name,
            'password' => $password,
            'status' => UserStatus::Active,
        ]);

        $tenant->update([
            'full_name' => $name ?: $tenant->full_name,
            'status' => TenantStatus::Active,
        ]);

        $this->audit->record('tenant.activated', $tenant, [
            'status' => TenantStatus::Invited->value,
        ], [
            'status' => TenantStatus::Active->value,
        ], 'Tenant');

        return $user;
    }

    /**
     * Normalize enum-backed fields so empty strings become null.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        foreach (['gender', 'birth_date', 'nik', 'notes', 'address', 'vehicle_type', 'vehicle_number', 'emergency_name', 'emergency_relationship', 'emergency_phone'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        unset($data['skip_invite']);

        return $data;
    }
}
