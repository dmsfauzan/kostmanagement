<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Admin\Payment\Show as PaymentShow;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PaymentRejectedNotification;
use App\Notifications\PaymentSubmittedNotification;
use App\Notifications\PaymentVerifiedNotification;
use App\Services\BillingService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $room = Room::factory()->create(['property_id' => $property->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'price' => 1500000]);
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
});

function submitPayment(): Payment
{
    return app(PaymentService::class)->submit(test()->invoice, [
        'amount' => 1500000,
        'payment_method_id' => test()->transfer->id,
    ], UploadedFile::fake()->image('bukti.jpg'));
}

it('notifies admins when a payment is submitted', function () {
    Notification::fake();

    submitPayment();

    Notification::assertSentTo($this->owner, PaymentSubmittedNotification::class);
});

it('notifies the tenant when a payment is verified', function () {
    $payment = submitPayment();

    Notification::fake();
    $this->actingAs($this->owner);
    app(PaymentService::class)->verify($payment);

    Notification::assertSentTo($this->user, PaymentVerifiedNotification::class);
});

it('notifies the tenant when a payment is rejected', function () {
    $payment = submitPayment();

    Notification::fake();
    $this->actingAs($this->owner);
    app(PaymentService::class)->reject($payment, 'Bukti tidak jelas');

    Notification::assertSentTo($this->user, PaymentRejectedNotification::class);
});

it('reverses the invoice when a verified payment is refunded', function () {
    $payment = submitPayment();

    $this->actingAs($this->owner);
    app(PaymentService::class)->verify($payment);
    expect($this->invoice->refresh()->status)->toBe(InvoiceStatus::Paid);

    app(PaymentService::class)->refund($payment);

    $invoice = $this->invoice->refresh();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($invoice->amount_paid)->toBe(0)
        ->and($invoice->status)->not->toBe(InvoiceStatus::Paid);

    $this->assertDatabaseHas('audit_logs', ['action' => 'payment.refunded']);
});

it('lets a tenant cancel their own pending payment', function () {
    $payment = submitPayment();

    $this->actingAs($this->user)
        ->post(route('tenant.payments.cancel', $payment))
        ->assertRedirect(route('tenant.payments'));

    expect($payment->refresh()->status)->toBe(PaymentStatus::Cancelled);
});

it('cancels a pending payment through the admin component', function () {
    $payment = submitPayment();

    $this->actingAs($this->owner);

    Livewire::test(PaymentShow::class, ['payment' => $payment])
        ->call('cancel')
        ->assertHasNoErrors();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Cancelled);
});
