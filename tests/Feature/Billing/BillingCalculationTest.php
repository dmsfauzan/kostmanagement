<?php

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Enums\LateFeeType;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\InvoiceNumberGenerator;
use App\Services\LateFeeCalculator;
use App\Services\ProrationCalculator;
use App\Services\SettingsService;
use Carbon\Carbon;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed(SettingsSeeder::class);

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $this->room = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
    ]);
    $this->tenant = Tenant::factory()->create(['property_id' => $this->property->id]);
    $this->lease = Lease::factory()->create([
        'tenant_id' => $this->tenant->id,
        'room_id' => $this->room->id,
        'property_id' => $this->property->id,
        'rent_amount' => 1500000,
        'deposit_amount' => 500000,
    ]);
});

function makeInvoice(array $attributes = []): Invoice
{
    return Invoice::factory()->create(array_merge([
        'property_id' => test()->property->id,
        'tenant_id' => test()->tenant->id,
        'lease_id' => test()->lease->id,
    ], $attributes));
}

it('recalculates invoice totals from its items', function () {
    $invoice = makeInvoice(['status' => InvoiceStatus::Issued]);

    $service = app(BillingService::class);
    $service->addItem($invoice, ['type' => InvoiceItemType::Rent, 'description' => 'Sewa', 'quantity' => 1, 'unit_price' => 1500000]);
    $service->addItem($invoice, ['type' => InvoiceItemType::Discount, 'description' => 'Diskon', 'quantity' => 1, 'unit_price' => 100000]);
    $service->addItem($invoice, ['type' => InvoiceItemType::LateFee, 'description' => 'Denda', 'quantity' => 1, 'unit_price' => 50000]);
    $service->addItem($invoice, ['type' => InvoiceItemType::Adjustment, 'description' => 'Koreksi', 'quantity' => 1, 'unit_price' => 20000, 'signed_amount' => -20000]);

    $invoice = $invoice->refresh();

    expect($invoice->subtotal)->toBe(1500000)
        ->and($invoice->discount_amount)->toBe(100000)
        ->and($invoice->late_fee_amount)->toBe(50000)
        ->and($invoice->adjustment_amount)->toBe(-20000)
        ->and($invoice->total_amount)->toBe(1430000)
        ->and($invoice->amount_due)->toBe(1430000);
});

it('computes a fixed-daily late fee deterministically', function () {
    $lease = $this->lease;
    $lease->update(['late_fee_type' => LateFeeType::FixedDaily, 'late_fee_value' => 25000]);

    $invoice = makeInvoice([
        'status' => InvoiceStatus::Issued,
        'subtotal' => 1500000,
        'due_date' => now()->subDays(2)->toDateString(),
    ]);
    $invoice->setRelation('lease', $lease->refresh());

    expect(app(LateFeeCalculator::class)->calculate($invoice))->toBe(50000);
});

it('computes a percentage late fee from basis points', function () {
    $lease = $this->lease;
    $lease->update(['late_fee_type' => LateFeeType::Percentage, 'late_fee_value' => 500]);

    $invoice = makeInvoice([
        'status' => InvoiceStatus::Issued,
        'subtotal' => 1500000,
        'due_date' => now()->subDays(3)->toDateString(),
    ]);
    $invoice->setRelation('lease', $lease->refresh());

    expect(app(LateFeeCalculator::class)->calculate($invoice))->toBe(75000);
});

it('returns zero late fee when not past due', function () {
    $invoice = makeInvoice([
        'status' => InvoiceStatus::Issued,
        'subtotal' => 1500000,
        'due_date' => now()->addDays(5)->toDateString(),
    ]);

    expect(app(LateFeeCalculator::class)->calculate($invoice))->toBe(0);
});

it('prorates rent for a mid-period move-in when enabled', function () {
    app(SettingsService::class)->set('billing.prorate_enabled', true, 'billing', 'boolean');
    app(SettingsService::class)->set('billing.prorate_basis_days', 30, 'billing', 'int');

    $start = Carbon::parse('2026-04-01');
    $end = Carbon::parse('2026-04-30');
    $moveIn = Carbon::parse('2026-04-16');

    $amount = app(ProrationCalculator::class)->rent(1500000, $start, $end, $moveIn);

    expect($amount)->toBe(750000);
});

it('charges full rent when proration is disabled', function () {
    app(SettingsService::class)->set('billing.prorate_enabled', false, 'billing', 'boolean');

    $amount = app(ProrationCalculator::class)->rent(
        1500000,
        Carbon::parse('2026-04-01'),
        Carbon::parse('2026-04-30'),
        Carbon::parse('2026-04-16'),
    );

    expect($amount)->toBe(1500000);
});

it('generates invoice numbers with the configured prefix', function () {
    $number = app(InvoiceNumberGenerator::class)->generate();

    expect($number)->toStartWith('INV-'.now()->year.'-');
});
