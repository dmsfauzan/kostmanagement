<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Admin\Payment\Show as PaymentShow;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\PaymentMethod;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $room = Room::factory()->create([
        'property_id' => $property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
        'price' => 1500000,
    ]);
    $this->user = User::factory()->withRole('tenant')->create();
    $this->tenant = Tenant::factory()->create(['property_id' => $property->id, 'user_id' => $this->user->id]);
    $lease = Lease::factory()->create([
        'tenant_id' => $this->tenant->id,
        'room_id' => $room->id,
        'property_id' => $property->id,
        'rent_amount' => 1500000,
        'status' => 'active',
    ]);

    $this->owner = User::factory()->withRole('owner')->create();

    $billing = app(BillingService::class);
    $this->invoice = $billing->createForLease($lease->refresh(), Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
    $billing->issue($this->invoice);

    $this->transfer = PaymentMethod::query()->where('code', 'transfer-bca')->firstOrFail();
    $this->cash = PaymentMethod::query()->where('code', 'tunai')->firstOrFail();
});

it('keeps the invoice unpaid while a payment is pending', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($this->invoice->refresh()->status)->toBe(InvoiceStatus::Issued)
        ->and($this->invoice->fresh()->amount_paid)->toBe(0);
});

it('requires proof for non-cash payments', function () {
    expect(fn () => app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->transfer->id,
    ]))->toThrow(InvalidArgumentException::class);
});

it('marks the invoice paid after a full payment is verified', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    $this->actingAs($this->owner);
    app(PaymentService::class)->verify($payment);

    $invoice = $this->invoice->refresh();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Verified)
        ->and($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->amount_paid)->toBe(1500000)
        ->and($invoice->amount_due)->toBe(0)
        ->and($invoice->paid_at)->not->toBeNull();
});

it('marks the invoice partially paid on a partial payment', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1000000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    $this->actingAs($this->owner);
    app(PaymentService::class)->verify($payment);

    $invoice = $this->invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::PartiallyPaid)
        ->and($invoice->amount_paid)->toBe(1000000)
        ->and($invoice->amount_due)->toBe(500000);
});

it('allows overpayment and clamps amount due to zero', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 2000000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    $this->actingAs($this->owner);
    app(PaymentService::class)->verify($payment);

    $invoice = $this->invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->amount_paid)->toBe(2000000)
        ->and($invoice->amount_due)->toBe(0);
});

it('records an audit log when a payment is verified', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    $this->actingAs($this->owner);
    app(PaymentService::class)->verify($payment);

    $this->assertDatabaseHas('audit_logs', ['action' => 'payment.verified', 'actor_id' => $this->owner->id]);
});

it('does not change the invoice when a payment is rejected', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    $this->actingAs($this->owner);
    app(PaymentService::class)->reject($payment, 'Bukti tidak jelas');

    expect($payment->refresh()->status)->toBe(PaymentStatus::Rejected)
        ->and($this->invoice->refresh()->status)->toBe(InvoiceStatus::Issued)
        ->and($this->invoice->fresh()->amount_paid)->toBe(0);
});

it('records a manual cash payment as verified and paid', function () {
    $this->actingAs($this->owner);

    app(PaymentService::class)->recordManual($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->cash->id,
    ]);

    expect($this->invoice->refresh()->status)->toBe(InvoiceStatus::Paid);
});

it('verifies a payment through the admin component', function () {
    $payment = app(PaymentService::class)->submit($this->invoice, [
        'amount' => 1500000,
        'payment_method_id' => $this->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));

    $this->actingAs($this->owner);

    Livewire::test(PaymentShow::class, ['payment' => $payment])
        ->call('verify')
        ->assertHasNoErrors();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Verified)
        ->and($this->invoice->refresh()->status)->toBe(InvoiceStatus::Paid);
});

it('forbids a technician from verifying payments', function () {
    $technician = User::factory()->withRole('technician')->create();

    expect($technician->can('payment.verify'))->toBeFalse()
        ->and($technician->can('payment.view'))->toBeFalse();
});

it('renders the admin payment pages', function () {
    $this->actingAs($this->owner);

    $this->get(route('admin.payments'))->assertOk()->assertSee('Pembayaran');
    $this->get(route('admin.payment-methods'))->assertOk()->assertSee('Metode Pembayaran');
});

it('renders the tenant payment pages', function () {
    $this->actingAs($this->user);

    $this->get(route('tenant.payments'))->assertOk();
    $this->get(route('tenant.payments.create'))->assertOk();
});
