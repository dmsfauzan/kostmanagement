<?php

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FinancialService;
use App\Services\OccupancyService;
use App\Services\RoomService;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);
});

it('caches the financial summary and invalidates it via flush', function () {
    $service = app(FinancialService::class);
    $first = $service->summary();

    expect($first['billed'])->toBe(0);

    $property = Property::factory()->create();
    $tenant = Tenant::factory()->create(['property_id' => $property->id]);
    $room = Room::factory()->create(['property_id' => $property->id]);

    // Billed still cached as zero even though new data exists via query builder.
    Invoice::factory()->create([
        'property_id' => $property->id,
        'tenant_id' => $tenant->id,
        'lease_id' => Lease::factory()->create(['tenant_id' => $tenant->id, 'room_id' => $room->id, 'property_id' => $property->id])->id,
        'subtotal' => 1000000,
        'total_amount' => 1000000,
        'amount_due' => 1000000,
        'issue_date' => now()->toDateString(),
    ]);

    expect($service->summary($property->id)['billed'])->toBe(1000000);

    $service->flush();

    expect(app(FinancialService::class)->summary($property->id)['billed'])->toBe(1000000);
});

it('caches occupancy and invalidates it on room changes', function () {
    $occupancy = app(OccupancyService::class);

    expect($occupancy->summary()['total'])->toBe(0);

    $property = Property::factory()->create();
    Room::factory()->create(['property_id' => $property->id]);

    // Cache still holds the old (empty) portfolio value for the null scope.
    expect(app(OccupancyService::class)->summary()['total'])->toBe(0);

    $occupancy->flush();

    expect($occupancy->summary()['total'])->toBe(1);

    $owner = User::factory()->withRole('owner')->create();
    $this->actingAs($owner);

    $room = Room::query()->first();
    app(RoomService::class)->delete($room);

    expect($occupancy->summary()['total'])->toBe(0);
});
