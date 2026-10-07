<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);
});

it('redirects an admin-role user to the admin dashboard', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
});

it('redirects a tenant to the tenant dashboard', function () {
    $tenant = User::factory()->withRole('tenant')->create();

    $this->actingAs($tenant)->get('/dashboard')->assertRedirect(route('tenant.dashboard'));
});

it('renders the admin dashboard with real user counts', function () {
    User::factory()->count(3)->withRole('tenant')->create();

    $owner = User::factory()->withRole('owner')->create();

    $this->actingAs($owner)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Total Pengguna');
});

it('renders the tenant dashboard', function () {
    $tenant = User::factory()->withRole('tenant')->create();

    $this->actingAs($tenant)
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertSee('Kamar Saya')
        ->assertSee('Tagihan Aktif');
});

it('renders the public home page for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Kelola kost Anda');
});
