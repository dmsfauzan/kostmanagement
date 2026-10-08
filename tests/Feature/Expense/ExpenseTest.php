<?php

use App\Livewire\Admin\Expense\Form as ExpenseForm;
use App\Models\Building;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\PaymentMethod;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use App\Services\FinancialService;
use App\Services\PaymentService;
use Database\Seeders\ExpenseCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class, ExpenseCategorySeeder::class]);

    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);

    $this->property = Property::factory()->create();
});

it('creates an expense through the form', function () {
    $category = ExpenseCategory::query()->first();

    Livewire::test(ExpenseForm::class)
        ->set('property_id', $this->property->id)
        ->set('expense_category_id', $category->id)
        ->set('amount', 750000)
        ->set('expense_date', now()->toDateString())
        ->set('vendor', 'PLN')
        ->call('save')
        ->assertHasNoErrors();

    expect(Expense::query()->where('vendor', 'PLN')->exists())->toBeTrue();
});

it('rejects a negative amount', function () {
    Livewire::test(ExpenseForm::class)
        ->set('property_id', $this->property->id)
        ->set('amount', -500)
        ->set('expense_date', now()->toDateString())
        ->call('save')
        ->assertHasErrors(['amount']);
});

it('computes a separate billed, collected and net cash flow', function () {
    $property = $this->property;
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $room = Room::factory()->create(['property_id' => $property->id, 'building_id' => $building->id, 'floor_id' => $floor->id]);
    $user = User::factory()->withRole('tenant')->create();
    $tenant = Tenant::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]);
    $lease = Lease::factory()->create(['tenant_id' => $tenant->id, 'room_id' => $room->id, 'property_id' => $property->id, 'status' => 'active']);

    $billing = app(BillingService::class);
    $invoice = $billing->createForLease($lease->refresh(), now()->copy()->firstOfMonth(), now()->copy()->endOfMonth()->startOfDay());
    $billing->issue($invoice);

    $method = PaymentMethod::query()->firstOrFail();
    $this->actingAs($this->owner);
    $payment = app(PaymentService::class)->recordManual($invoice->refresh(), [
        'amount' => 500000,
        'payment_method_id' => $method->id,
    ]);

    Expense::factory()->create(['property_id' => $property->id, 'amount' => 200000, 'expense_date' => now()->toDateString()]);

    $summary = app(FinancialService::class)->summary($property->id);

    expect($summary['billed'])->toBe($invoice->refresh()->total_amount)
        ->and($summary['collected'])->toBe(500000)
        ->and($summary['outstanding'])->toBe($invoice->refresh()->amount_due)
        ->and($summary['expenses'])->toBe(200000)
        ->and($summary['net_cash_flow'])->toBe(300000);
});

it('renders the dashboard and expense pages', function () {
    $this->get(route('admin.dashboard'))->assertOk()->assertSee('Perlu Tindakan');
    $this->get(route('admin.expenses'))->assertOk()->assertSee('Pengeluaran');
    $this->get(route('admin.expenses.create'))->assertOk();
});

it('forbids a technician from creating expenses', function () {
    $technician = User::factory()->withRole('technician')->create();

    expect($technician->can('expense.create'))->toBeFalse();
});
