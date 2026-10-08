<?php

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Livewire\Admin\Invoice\Form as InvoiceForm;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use App\Services\BillingService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);
    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);

    $property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $room = Room::factory()->create([
        'property_id' => $property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
        'price' => 1500000,
    ]);
    $this->tenantUser = User::factory()->withRole('tenant')->create();
    $tenant = Tenant::factory()->create([
        'property_id' => $property->id,
        'user_id' => $this->tenantUser->id,
    ]);
    $this->lease = Lease::factory()->create([
        'tenant_id' => $tenant->id,
        'room_id' => $room->id,
        'property_id' => $property->id,
        'rent_amount' => 1500000,
        'status' => 'active',
    ]);
});

it('creates a manual invoice through the form', function () {
    Livewire::test(InvoiceForm::class)
        ->set('lease_id', $this->lease->id)
        ->set('period_start', '2026-09-01')
        ->set('period_end', '2026-09-30')
        ->call('save')
        ->assertHasNoErrors();

    $invoice = Invoice::query()->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->total_amount)->toBe(1500000);
});

it('notifies the tenant when an invoice is issued', function () {
    Notification::fake();

    $invoice = app(BillingService::class)->createForLease(
        $this->lease->refresh(),
        Carbon::parse('2026-09-01'),
        Carbon::parse('2026-09-30'),
    );

    app(BillingService::class)->issue($invoice);

    Notification::assertSentTo($this->tenantUser, InvoiceIssuedNotification::class);
});

it('prevents issuing an already-issued invoice', function () {
    $invoice = app(BillingService::class)->createForLease(
        $this->lease->refresh(),
        Carbon::parse('2026-08-01'),
        Carbon::parse('2026-08-31'),
    );
    app(BillingService::class)->issue($invoice);

    expect(fn () => app(BillingService::class)->issue($invoice->refresh()))
        ->toThrow(InvalidArgumentException::class);
});

it('voids an invoice without deleting its row', function () {
    $invoice = app(BillingService::class)->createForLease(
        $this->lease->refresh(),
        Carbon::parse('2026-07-01'),
        Carbon::parse('2026-07-31'),
    );

    app(BillingService::class)->void($invoice, 'Duplikasi input');

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Void)
        ->and(Invoice::withTrashed()->whereKey($invoice->id)->exists())->toBeTrue();
});

it('prevents duplicate recurring invoices for the same lease and period', function () {
    $service = app(BillingService::class);

    $service->createForLease($this->lease->refresh(), Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'));

    expect(fn () => $service->createForLease($this->lease->refresh(), Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30')))
        ->toThrow(InvalidArgumentException::class);
});

it('allows multiple manual invoices for the same lease', function () {
    $service = app(BillingService::class);
    $lease = $this->lease->refresh();

    $first = $service->createManual($lease, [[
        'type' => InvoiceItemType::Rent, 'description' => 'Sewa A', 'quantity' => 1, 'unit_price' => 1500000,
    ]]);
    $second = $service->createManual($lease, [[
        'type' => InvoiceItemType::Other, 'description' => 'Lainnya', 'quantity' => 1, 'unit_price' => 50000,
    ]]);

    expect($first->billing_period)->toBeNull()
        ->and($second->billing_period)->toBeNull()
        ->and(Invoice::query()->where('lease_id', $lease->id)->count())->toBe(2);
});

it('adds and removes items while recalculating totals', function () {
    $invoice = app(BillingService::class)->createForLease(
        $this->lease->refresh(),
        Carbon::parse('2026-05-01'),
        Carbon::parse('2026-05-31'),
    );

    app(BillingService::class)->addItem($invoice->refresh(), [
        'type' => InvoiceItemType::Electricity, 'description' => 'Listrik', 'quantity' => 1, 'unit_price' => 150000,
    ]);

    expect($invoice->refresh()->total_amount)->toBe(1650000);

    app(BillingService::class)->removeItem($invoice->refresh(), $invoice->items()->where('type', 'electricity')->firstOrFail()->id);

    expect($invoice->refresh()->total_amount)->toBe(1500000);
});

it('forbids technicians from admin invoices', function () {
    $technician = User::factory()->withRole('technician')->create();

    $this->actingAs($technician)
        ->get(route('admin.invoices'))
        ->assertForbidden();
});
