<?php

use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\MaintenanceAssignedNotification;
use App\Notifications\MaintenanceCreatedNotification;
use App\Notifications\MaintenanceSlaNotification;
use App\Notifications\MaintenanceStatusNotification;
use App\Services\MaintenanceService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

    $this->property = Property::factory()->create();
    $this->tenantUser = User::factory()->withRole('tenant')->create();
    $this->tenant = Tenant::factory()->create([
        'property_id' => $this->property->id,
        'user_id' => $this->tenantUser->id,
    ]);
    $this->owner = User::factory()->withRole('owner')->create();
    $this->technician = User::factory()->withRole('technician')->create();
});

function makeTicket(): MaintenanceTicket
{
    return app(MaintenanceService::class)->create(test()->tenant, [
        'title' => 'Keluhan uji',
        'category' => 'other',
        'description' => 'Deskripsi uji',
        'priority' => 'high',
    ]);
}

it('notifies staff when a ticket is created', function () {
    Notification::fake();

    makeTicket();

    Notification::assertSentTo($this->owner, MaintenanceCreatedNotification::class);
});

it('notifies the assignee when a ticket is assigned', function () {
    $ticket = makeTicket();

    Notification::fake();
    app(MaintenanceService::class)->assign($ticket, $this->technician->id);

    Notification::assertSentTo($this->technician, MaintenanceAssignedNotification::class);
});

it('notifies the tenant when the status changes', function () {
    $ticket = makeTicket();

    Notification::fake();
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Acknowledged);

    Notification::assertSentTo($this->tenantUser, MaintenanceStatusNotification::class);
});

it('marks and notifies SLA breaches once', function () {
    $ticket = makeTicket();
    $ticket->update(['sla_due_at' => now()->subHour(), 'sla_notified_at' => null]);

    Notification::fake();

    $this->artisan('maintenance:check-sla')->assertSuccessful();
    $this->artisan('maintenance:check-sla')->assertSuccessful();

    expect($ticket->refresh()->sla_notified_at)->not->toBeNull();
    Notification::assertSentTo($this->owner, MaintenanceSlaNotification::class);
});

it('does not notify SLA for closed tickets', function () {
    $ticket = makeTicket();
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Acknowledged);
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::InProgress);
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Resolved);
    app(MaintenanceService::class)->transition($ticket, MaintenanceStatus::Closed);

    $ticket->update(['sla_due_at' => now()->subHour()]);

    Notification::fake();
    $this->artisan('maintenance:check-sla')->assertSuccessful();

    Notification::assertNotSentTo($this->owner, MaintenanceSlaNotification::class);
});
