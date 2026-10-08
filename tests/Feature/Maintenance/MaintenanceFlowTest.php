<?php

use App\Enums\MaintenanceStatus;
use App\Livewire\Tenant\Maintenance\Create as TenantMaintenanceCreate;
use App\Livewire\Tenant\Maintenance\Show;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MaintenanceService;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $room = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
    ]);

    $this->user = User::factory()->withRole('tenant')->create();
    $this->tenant = Tenant::factory()->create([
        'property_id' => $this->property->id,
        'user_id' => $this->user->id,
    ]);
    $lease = Lease::factory()->create([
        'tenant_id' => $this->tenant->id,
        'room_id' => $room->id,
        'property_id' => $this->property->id,
        'status' => 'active',
    ]);

    $this->owner = User::factory()->withRole('owner')->create();
    $this->technician = User::factory()->withRole('technician')->create();
});

it('allows a tenant to create a ticket', function () {
    $this->actingAs($this->user);

    Livewire::test(TenantMaintenanceCreate::class)
        ->set('title', 'AC bocor')
        ->set('category', 'ac')
        ->set('priority', 'high')
        ->set('description', 'AC menetes air sejak pagi')
        ->call('save')
        ->assertHasNoErrors();

    $ticket = MaintenanceTicket::query()->first();
    expect($ticket)->not->toBeNull()
        ->and($ticket->status)->toBe(MaintenanceStatus::Submitted)
        ->and($ticket->tenant_id)->toBe($this->tenant->id)
        ->and($ticket->ticket_number)->toStartWith('MNT-');
});

it('validates required fields on create', function () {
    $this->actingAs($this->user);

    Livewire::test(TenantMaintenanceCreate::class)
        ->set('title', '')
        ->set('description', '')
        ->call('save')
        ->assertHasErrors(['title', 'description']);
});

it('enforces the correct status transitions', function () {
    $ticket = app(MaintenanceService::class)->create($this->tenant, [
        'title' => 'Lampu mati',
        'category' => 'electricity',
        'description' => 'Lampu kamar mati',
        'priority' => 'high',
    ]);

    expect(fn () => app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Resolved))
        ->toThrow(InvalidArgumentException::class);

    $ticket = app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Acknowledged);
    $ticket = app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::InProgress);
    $ticket = app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Resolved);

    expect($ticket->status)->toBe(MaintenanceStatus::Resolved)
        ->and($ticket->statusHistories()->count())->toBe(4);
});

it('lets a tenant confirm a resolved ticket', function () {
    $ticket = app(MaintenanceService::class)->create($this->tenant, [
        'title' => 'Keran bocor',
        'category' => 'water',
        'description' => 'Keran wastafel bocor',
        'priority' => 'medium',
    ]);
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Acknowledged);
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::InProgress);
    $ticket = app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Resolved);

    $this->actingAs($this->user);
    $component = Livewire::test(Show::class, ['ticket' => $ticket])
        ->call('confirmResolved');

    $component->assertHasNoErrors();

    expect(MaintenanceTicket::query()->find($ticket->id)->status)->toBe(MaintenanceStatus::Closed);
});

it('hides internal comments from tenants', function () {
    $ticket = app(MaintenanceService::class)->create($this->tenant, [
        'title' => 'Wifi lemot',
        'category' => 'wifi',
        'description' => 'Wifi tidak stabil',
        'priority' => 'high',
    ]);
    app(MaintenanceService::class)->addComment($ticket, $this->owner, 'Cek provider', true);
    app(MaintenanceService::class)->addComment($ticket, $this->owner, 'Perbaikan selesai', false);

    $this->actingAs($this->user);
    $component = Livewire::test(Show::class, ['ticket' => $ticket]);

    $html = $component->html();
    expect($html)->not->toContain('Cek provider')
        ->toContain('Perbaikan selesai');
});

it('blocks tenants from viewing other tenants tickets', function () {
    $otherTenant = Tenant::factory()->create(['property_id' => $this->property->id]);
    $ticket = app(MaintenanceService::class)->create($otherTenant, [
        'title' => 'Orang lain',
        'category' => 'other',
        'description' => 'Bukan milik user',
        'priority' => 'low',
    ]);

    $this->actingAs($this->user)
        ->get(route('tenant.maintenance.show', $ticket))
        ->assertNotFound();
});

it('lets a technician update a ticket', function () {
    $ticket = app(MaintenanceService::class)->create($this->tenant, [
        'title' => 'Pintu macet',
        'category' => 'facility',
        'description' => 'Pintu kamar harus didorong keras',
        'priority' => 'medium',
    ]);

    $this->actingAs($this->technician);

    expect($this->technician->can('maintenance.update'))->toBeTrue()
        ->and($this->technician->can('maintenance.view'))->toBeTrue();

    $this->get(route('admin.maintenance.show', $ticket))->assertOk();

    app(MaintenanceService::class)->assign($ticket, $this->technician->id);

    expect($ticket->refresh()->assigned_to)->toBe($this->technician->id);
});

it('renders the maintenance pages smoke test', function () {
    $ticket = app(MaintenanceService::class)->create($this->tenant, [
        'title' => 'Smoke test',
        'category' => 'other',
        'description' => 'Smoke test body',
        'priority' => 'low',
    ]);

    $this->actingAs($this->user);
    $this->get(route('tenant.maintenance'))->assertOk();
    $this->get(route('tenant.maintenance.create'))->assertOk();
    $this->get(route('tenant.maintenance.show', $ticket))->assertOk();

    $this->actingAs($this->owner);
    $this->get(route('admin.maintenance'))->assertOk();
    $this->get(route('admin.maintenance.show', $ticket))->assertOk();
});

it('detects SLA overdue tickets', function () {
    $ticket = app(MaintenanceService::class)->create($this->tenant, [
        'title' => 'Sla overdue',
        'category' => 'other',
        'description' => 'Sla overdue body',
        'priority' => 'low',
    ]);

    $ticket->update(['sla_due_at' => now()->subHour()]);

    expect($ticket->refresh()->isSlaOverdue())->toBeTrue()
        ->and($ticket->refresh()->daysUntilSla())->toBeLessThanOrEqual(0);
});
