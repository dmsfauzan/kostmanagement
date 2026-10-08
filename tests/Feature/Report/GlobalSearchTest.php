<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\GlobalSearchService;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $room = Room::factory()->create(['property_id' => $this->property->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'number' => 'Z-999']);
    $tenant = Tenant::factory()->create(['property_id' => $this->property->id, 'full_name' => 'Zenith Wibowo']);
    Lease::factory()->create(['tenant_id' => $tenant->id, 'room_id' => $room->id, 'property_id' => $this->property->id, 'status' => 'active']);
    $invoice = Invoice::factory()->create(['property_id' => $this->property->id, 'tenant_id' => $tenant->id, 'lease_id' => 1, 'invoice_number' => 'INV-ZENITH-1']);
    Payment::factory()->create(['property_id' => $this->property->id, 'invoice_id' => $invoice->id, 'tenant_id' => $tenant->id, 'reference' => 'ZENITHREF']);
});

it('finds a tenant and labels the entity type', function () {
    $groups = app(GlobalSearchService::class)->search('Zenith');

    expect($groups)->toHaveKey('tenant')
        ->and($groups['tenant']['label'])->toBe('Penghuni')
        ->and($groups['tenant']['items'][0]['label'])->toBe('Zenith Wibowo');
});

it('finds rooms, invoices and payments by keyword', function () {
    $service = app(GlobalSearchService::class);

    expect($service->search('Z-999'))->toHaveKey('room');
    expect($service->search('INV-ZENITH-1'))->toHaveKey('invoice');
    expect($service->search('ZENITHREF'))->toHaveKey('payment');
});

it('returns nothing for a short term', function () {
    expect(app(GlobalSearchService::class)->search('Z'))->toBe([]);
});
