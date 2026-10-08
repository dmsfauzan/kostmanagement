<?php

use App\Livewire\Admin\Tenant\Form;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);
});

it('creates a tenant and provisions an invited portal account', function () {
    Notification::fake();

    Livewire::test(Form::class)
        ->set('full_name', 'Andi Wijaya')
        ->set('phone', '081234567890')
        ->set('email', 'andi@example.test')
        ->call('save')
        ->assertHasNoErrors();

    $tenant = Tenant::query()->where('email', 'andi@example.test')->firstOrFail();
    $user = User::query()->where('email', 'andi@example.test')->firstOrFail();

    expect($user->hasRole('tenant'))->toBeTrue()
        ->and($tenant->user_id)->toBe($user->id)
        ->and($tenant->status->value)->toBe('invited');

    Notification::assertSentTo($user, TenantInvitationNotification::class);
});

it('can create a tenant without sending an invite', function () {
    Notification::fake();

    Livewire::test(Form::class)
        ->set('full_name', 'Tanpa Akun')
        ->set('phone', '081200000000')
        ->set('email', 'noinvite@example.test')
        ->set('skip_invite', true)
        ->call('save')
        ->assertHasNoErrors();

    $tenant = Tenant::query()->where('email', 'noinvite@example.test')->firstOrFail();

    expect($tenant->user_id)->toBeNull();
    Notification::assertNothingSent();
});

it('validates required tenant fields', function () {
    Livewire::test(Form::class)
        ->set('full_name', '')
        ->set('phone', '')
        ->set('email', '')
        ->call('save')
        ->assertHasErrors(['full_name', 'phone', 'email']);
});

it('forbids technicians from creating tenants', function () {
    $technician = User::factory()->withRole('technician')->create();

    $this->actingAs($technician)
        ->get(route('admin.tenants.create'))
        ->assertForbidden();
});

it('renders tenant index and show pages', function () {
    $tenant = Tenant::factory()->create();

    $this->get(route('admin.tenants'))->assertOk()->assertSee('Penghuni');
    $this->get(route('admin.tenants.show', $tenant))->assertOk()->assertSee($tenant->full_name);
});
