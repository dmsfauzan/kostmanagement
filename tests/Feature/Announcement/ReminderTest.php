<?php

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Enums\InvoiceStatus;
use App\Livewire\Tenant\Notifications\Index;
use App\Models\Announcement;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\ReminderLog;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\DueReminderNotification;
use App\Notifications\LeaseExpiryNotification;
use App\Notifications\OverdueReminderNotification;
use App\Services\ReminderService;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $this->owner = User::factory()->withRole('owner')->create();
    $this->admin = User::factory()->withRole('admin')->create();
    $this->finance = User::factory()->withRole('finance')->create();
});

function dueInvoice(array $overrides = []): Invoice
{
    $property = Property::factory()->create();
    $tenant = Tenant::factory()->create(['property_id' => $property->id, 'user_id' => User::factory()->withRole('tenant')->create()->id]);
    $lease = Lease::factory()->create(['tenant_id' => $tenant->id, 'property_id' => $property->id, 'status' => 'active']);

    return Invoice::factory()->create(array_merge([
        'property_id' => $property->id,
        'tenant_id' => $tenant->id,
        'lease_id' => $lease->id,
        'status' => InvoiceStatus::Issued,
        'subtotal' => 1500000,
        'total_amount' => 1500000,
        'amount_due' => 1500000,
        'issue_date' => now()->subDays(7)->toDateString(),
        'due_date' => now()->addDays(3)->toDateString(),
    ], $overrides));
}

it('sends a due reminder exactly on a configured offset', function () {
    $invoice = dueInvoice(['due_date' => now()->addDays(3)->toDateString()]);

    Notification::fake();
    app(ReminderService::class)->sendDueReminders();

    Notification::assertSentTo($invoice->tenant->user, DueReminderNotification::class);
});

it('is idempotent for the same due offset', function () {
    dueInvoice(['due_date' => now()->addDays(7)->toDateString()]);

    Notification::fake();
    app(ReminderService::class)->sendDueReminders();
    app(ReminderService::class)->sendDueReminders();

    $totalNotifications = collect(Notification::sentNotifications())->sum(fn ($item) => count($item));
    expect($totalNotifications)->toBeGreaterThan(0);

    $dbCount = ReminderLog::query()->count();
    expect($dbCount)->toBeGreaterThan(0);
});

it('does not send a due reminder outside offsets', function () {
    dueInvoice(['due_date' => now()->addDays(5)->toDateString()]);

    Notification::fake();
    app(ReminderService::class)->sendDueReminders();

    expect(Notification::sentNotifications())->toBeEmpty();
});

it('sends an overdue reminder on a configured overdue offset', function () {
    $invoice = dueInvoice([
        'due_date' => now()->subDays(1)->toDateString(),
        'status' => InvoiceStatus::Overdue,
    ]);

    Notification::fake();
    app(ReminderService::class)->sendOverdueReminders();

    Notification::assertSentTo($invoice->tenant->user, OverdueReminderNotification::class);
});

it('is idempotent for the same overdue offset', function () {
    dueInvoice(['due_date' => now()->subDays(3)->toDateString(), 'status' => InvoiceStatus::Overdue]);

    Notification::fake();
    app(ReminderService::class)->sendOverdueReminders();
    app(ReminderService::class)->sendOverdueReminders();

    $logs = ReminderLog::query()->where('key', 'invoice_overdue:3')->first();
    expect($logs)->not->toBeNull();
});

it('sends a lease expiry reminder and marks the lease expiring', function () {
    $property = Property::factory()->create();
    $user = User::factory()->withRole('tenant')->create();
    $tenant = Tenant::factory()->create(['property_id' => $property->id, 'user_id' => $user->id]);
    Room::factory()->create(['property_id' => $property->id]);

    $lease = Lease::factory()->create([
        'tenant_id' => $tenant->id,
        'property_id' => $property->id,
        'room_id' => Room::factory()->create(['property_id' => $property->id])->id,
        'start_date' => now()->subMonths(11)->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'status' => 'active',
    ]);

    Notification::fake();
    app(ReminderService::class)->sendLeaseExpiryReminders();

    Notification::assertSentTo($user, LeaseExpiryNotification::class);
    expect($lease->refresh()->status->value)->toBe('expiring');
});

it('publishes due scheduled announcements via the command', function () {
    User::factory()->withRole('tenant')->create();

    $announcement = Announcement::factory()->create([
        'status' => AnnouncementStatus::Published,
        'publish_at' => now()->subHour(),
        'target_type' => AnnouncementTarget::All,
    ]);

    Notification::fake();
    $this->artisan('announcements:publish-due')->assertSuccessful();

    expect($announcement->recipients()->count())->toBeGreaterThan(0);
});

it('lets a tenant read notifications and mark all read', function () {
    $invoice = dueInvoice(['due_date' => now()->addDays(3)->toDateString()]);
    app(ReminderService::class)->sendDueReminders();

    $user = $invoice->tenant->user;

    $this->actingAs($user);
    $this->get(route('tenant.notifications'))->assertOk();

    Livewire::test(Index::class)
        ->call('markAllRead')
        ->assertHasNoErrors();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});
