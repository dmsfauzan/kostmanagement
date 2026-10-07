<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);
});

it('redirects guests from admin dashboard to login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('redirects guests from tenant dashboard to login', function () {
    $this->get(route('tenant.dashboard'))->assertRedirect(route('login'));
});

it('allows owner, admin, finance and technician into the admin area', function (string $role) {
    $user = User::factory()->withRole($role)->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
})->with(['owner', 'admin', 'finance', 'technician']);

it('forbids tenants from the admin area', function () {
    $tenant = User::factory()->withRole('tenant')->create();

    $this->actingAs($tenant)->get(route('admin.dashboard'))->assertForbidden();
});

it('allows only tenants into the tenant portal', function () {
    $tenant = User::factory()->withRole('tenant')->create();
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($tenant)->get(route('tenant.dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('tenant.dashboard'))->assertForbidden();
});

it('blocks suspended users from protected routes', function () {
    $user = User::factory()->withRole('admin')->suspended()->create();

    $this->actingAs($user)->get('/dashboard')->assertForbidden();
});

it('scopes permissions correctly per role', function () {
    $finance = User::factory()->withRole('finance')->create();
    $technician = User::factory()->withRole('technician')->create();

    expect($finance->can('payment.verify'))->toBeTrue()
        ->and($finance->can('settings.update'))->toBeFalse()
        ->and($technician->can('payment.view'))->toBeFalse()
        ->and($technician->can('maintenance.update'))->toBeTrue();
});

it('gives owners every ability', function () {
    $owner = User::factory()->withRole('owner')->create();

    expect($owner->can('settings.update'))->toBeTrue()
        ->and($owner->can('anything.at.all'))->toBeTrue();
});
