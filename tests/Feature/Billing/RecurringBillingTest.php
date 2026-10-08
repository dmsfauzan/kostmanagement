<?php

use App\Enums\InvoiceStatus;
use App\Enums\LateFeeType;
use App\Enums\RoomStatus;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);
    Notification::fake();

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $this->room = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
        'status' => RoomStatus::Occupied,
    ]);
    $user = User::factory()->withRole('tenant')->create();
    $this->tenant = Tenant::factory()->create(['property_id' => $this->property->id, 'user_id' => $user->id]);
});

function activeLease(array $overrides = []): Lease
{
    return Lease::factory()->create(array_merge([
        'tenant_id' => test()->tenant->id,
        'room_id' => test()->room->id,
        'property_id' => test()->property->id,
        'rent_amount' => 1500000,
        'deposit_amount' => 500000,
        'status' => 'active',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'late_fee_type' => LateFeeType::None,
        'late_fee_value' => 0,
    ], $overrides));
}

it('generates one invoice per active lease and is idempotent', function () {
    $lease = activeLease();

    $this->artisan('billing:generate-invoices', ['--period' => '2026-10'])->assertSuccessful();
    $this->artisan('billing:generate-invoices', ['--period' => '2026-10'])->assertSuccessful();

    $invoices = Invoice::query()->where('lease_id', $lease->id)->where('billing_period', '2026-10')->get();

    expect($invoices)->toHaveCount(1)
        ->and($invoices->first()->status)->toBe(InvoiceStatus::Issued)
        ->and($invoices->first()->total_amount)->toBe(1500000);
});

it('does not generate invoices for inactive leases', function () {
    activeLease(['status' => 'draft']);

    $this->artisan('billing:generate-invoices', ['--period' => '2026-10'])->assertSuccessful();

    expect(Invoice::query()->count())->toBe(0);
});

it('does not generate invoices for leases outside the period', function () {
    activeLease(['start_date' => '2027-01-01', 'end_date' => '2027-12-31']);

    $this->artisan('billing:generate-invoices', ['--period' => '2026-10'])->assertSuccessful();

    expect(Invoice::query()->count())->toBe(0);
});

it('marks unpaid invoices past due as overdue', function () {
    $invoice = Invoice::factory()->create([
        'property_id' => $this->property->id,
        'tenant_id' => $this->tenant->id,
        'lease_id' => activeLease()->id,
        'status' => InvoiceStatus::Issued,
        'due_date' => now()->subDays(3)->toDateString(),
    ]);

    $this->artisan('billing:mark-overdue')->assertSuccessful();

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Overdue);
});

it('applies a deterministic late fee without doubling on re-run', function () {
    $lease = activeLease(['late_fee_type' => LateFeeType::FixedDaily, 'late_fee_value' => 25000]);

    $invoice = app(BillingService::class)->createForLease(
        $lease->refresh(),
        Carbon::parse('2026-10-01'),
        Carbon::parse('2026-10-31'),
    );
    app(BillingService::class)->issue($invoice);

    $invoice->update(['due_date' => now()->subDays(2)->toDateString(), 'status' => InvoiceStatus::Overdue]);

    $this->artisan('billing:apply-late-fees')->assertSuccessful();
    expect($invoice->refresh()->late_fee_amount)->toBe(50000)
        ->and($invoice->total_amount)->toBe(1550000);

    $this->artisan('billing:apply-late-fees')->assertSuccessful();
    expect($invoice->refresh()->late_fee_amount)->toBe(50000)
        ->and($invoice->total_amount)->toBe(1550000);
});
