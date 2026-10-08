<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceAttachment;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $this->room = Room::factory()->create(['property_id' => $this->property->id, 'building_id' => $building->id, 'floor_id' => $floor->id]);

    $this->userA = User::factory()->withRole('tenant')->create();
    $this->userB = User::factory()->withRole('tenant')->create();

    $this->tenantA = Tenant::factory()->create(['property_id' => $this->property->id, 'user_id' => $this->userA->id]);
    $this->tenantB = Tenant::factory()->create(['property_id' => $this->property->id, 'user_id' => $this->userB->id]);

    $this->leaseB = Lease::factory()->create(['tenant_id' => $this->tenantB->id, 'room_id' => $this->room->id, 'property_id' => $this->property->id, 'status' => 'active']);
});

it('sends baseline security headers', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('blocks a tenant from downloading another tenant payment proof', function () {
    Storage::fake(config('kost.disk.private'));

    $invoice = Invoice::factory()->create(['property_id' => $this->property->id, 'tenant_id' => $this->tenantB->id, 'lease_id' => $this->leaseB->id]);
    $method = PaymentMethod::query()->firstOrFail();
    $payment = Payment::factory()->create([
        'property_id' => $this->property->id,
        'invoice_id' => $invoice->id,
        'tenant_id' => $this->tenantB->id,
        'payment_method_id' => $method->id,
        'proof_path' => 'payments/x/proof.jpg',
    ]);
    Storage::disk(config('kost.disk.private'))->put($payment->proof_path, 'dummy');

    $this->actingAs($this->userA)
        ->get(route('tenant.payments.proof', $payment))
        ->assertNotFound();
});

it('allows a tenant to download their own payment proof', function () {
    Storage::fake(config('kost.disk.private'));

    $invoice = Invoice::factory()->create(['property_id' => $this->property->id, 'tenant_id' => $this->tenantA->id]);
    $payment = Payment::factory()->create([
        'property_id' => $this->property->id,
        'invoice_id' => $invoice->id,
        'tenant_id' => $this->tenantA->id,
        'proof_path' => 'payments/y/proof.jpg',
    ]);
    Storage::disk(config('kost.disk.private'))->put($payment->proof_path, 'dummy');

    $this->actingAs($this->userA)
        ->get(route('tenant.payments.proof', $payment))
        ->assertOk();
});

it('blocks a tenant from another tenant maintenance attachment', function () {
    Storage::fake(config('kost.disk.private'));

    $ticket = MaintenanceTicket::factory()->create([
        'property_id' => $this->property->id,
        'tenant_id' => $this->tenantB->id,
    ]);
    $attachment = MaintenanceAttachment::create([
        'ticket_id' => $ticket->id,
        'path' => 'maintenance/z/photo.jpg',
        'uploaded_by' => $this->userB->id,
    ]);
    Storage::disk(config('kost.disk.private'))->put($attachment->path, 'dummy');

    $this->actingAs($this->userA)
        ->get(route('tenant.maintenance.attachment', $attachment))
        ->assertNotFound();
});

it('forbids a technician from exporting reports', function () {
    $technician = User::factory()->withRole('technician')->create();

    $this->actingAs($technician)
        ->get(route('admin.reports.export', ['type' => 'occupancy', 'format' => 'csv']))
        ->assertForbidden();
});

it('enforces invoice policy record ownership', function () {
    $own = Invoice::factory()->create(['property_id' => $this->property->id, 'tenant_id' => $this->tenantA->id]);
    $other = Invoice::factory()->create(['property_id' => $this->property->id, 'tenant_id' => $this->tenantB->id]);

    expect(Gate::forUser($this->userA)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($this->userA)->allows('view', $other))->toBeFalse();
});

it('lets owners view any invoice and tenants only their own', function () {
    $owner = User::factory()->withRole('owner')->create();
    $invoice = Invoice::factory()->create(['property_id' => $this->property->id, 'tenant_id' => $this->tenantB->id]);

    expect(Gate::forUser($owner)->allows('view', $invoice))->toBeTrue()
        ->and(Gate::forUser($this->userA)->allows('view', $invoice))->toBeFalse();
});
