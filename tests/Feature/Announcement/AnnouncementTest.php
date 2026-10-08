<?php

use App\Enums\AnnouncementTarget;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use App\Services\AnnouncementService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->property = Property::factory()->create();
    $this->building = Building::factory()->create(['property_id' => $this->property->id]);
    $this->floor = Floor::factory()->create(['building_id' => $this->building->id]);

    $this->roomA = Room::factory()->create(['property_id' => $this->property->id, 'building_id' => $this->building->id, 'floor_id' => $this->floor->id, 'number' => 'A-101']);
    $this->roomB = Room::factory()->create(['property_id' => $this->property->id, 'building_id' => $this->building->id, 'floor_id' => $this->floor->id, 'number' => 'A-102']);

    $this->userA = User::factory()->withRole('tenant')->create();
    $this->userB = User::factory()->withRole('tenant')->create();

    $tenantA = Tenant::factory()->create(['property_id' => $this->property->id, 'user_id' => $this->userA->id]);
    $tenantB = Tenant::factory()->create(['property_id' => $this->property->id, 'user_id' => $this->userB->id]);

    Lease::factory()->create(['tenant_id' => $tenantA->id, 'room_id' => $this->roomA->id, 'property_id' => $this->property->id, 'status' => 'active']);
    Lease::factory()->create(['tenant_id' => $tenantB->id, 'room_id' => $this->roomB->id, 'property_id' => $this->property->id, 'status' => 'active']);

    $this->owner = User::factory()->withRole('owner')->create();
});

it('publishes an announcement to all tenant users', function () {
    Notification::fake();

    $announcement = app(AnnouncementService::class)->create([
        'title' => 'Umum', 'body' => 'Untuk semua', 'target_type' => AnnouncementTarget::All,
    ]);
    app(AnnouncementService::class)->publish($announcement);

    expect($announcement->refresh()->status->value)->toBe('published')
        ->and($announcement->recipients()->count())->toBe(2);
    Notification::assertSentTo($this->userA, AnnouncementPublishedNotification::class);
});

it('targets recipients by room', function () {
    $announcement = app(AnnouncementService::class)->create([
        'title' => 'Kamar A-101', 'body' => 'Hanya A-101', 'target_type' => AnnouncementTarget::Room, 'target_id' => $this->roomA->id,
    ]);
    app(AnnouncementService::class)->publish($announcement);

    $ids = $announcement->recipients()->pluck('user_id')->all();

    expect($ids)->toBe([$this->userA->id]);
});

it('targets recipients by tenant', function () {
    $tenantB = Tenant::query()->where('user_id', $this->userB->id)->first();

    $announcement = app(AnnouncementService::class)->create([
        'title' => 'Khusus', 'body' => 'Hanya B', 'target_type' => AnnouncementTarget::Tenant, 'target_id' => $tenantB->id,
    ]);
    app(AnnouncementService::class)->publish($announcement);

    expect($announcement->recipients()->pluck('user_id')->all())->toBe([$this->userB->id]);
});

it('shows a tenant only their announcements', function () {
    $toA = app(AnnouncementService::class)->create(['title' => 'Untuk A', 'body' => 'x', 'target_type' => AnnouncementTarget::Room, 'target_id' => $this->roomA->id]);
    app(AnnouncementService::class)->publish($toA);

    $toB = app(AnnouncementService::class)->create(['title' => 'Untuk B', 'body' => 'y', 'target_type' => AnnouncementTarget::Room, 'target_id' => $this->roomB->id]);
    app(AnnouncementService::class)->publish($toB);

    $this->actingAs($this->userA)
        ->get(route('tenant.announcements'))
        ->assertOk()
        ->assertSee('Untuk A')
        ->assertDontSee('Untuk B');
});

it('marks an announcement read when opened', function () {
    $announcement = app(AnnouncementService::class)->create(['title' => 'Baca', 'body' => 'x', 'target_type' => AnnouncementTarget::Room, 'target_id' => $this->roomA->id]);
    app(AnnouncementService::class)->publish($announcement);

    $this->actingAs($this->userA)
        ->get(route('tenant.announcements.show', $announcement))
        ->assertOk();

    $recipient = $announcement->recipients()->where('user_id', $this->userA->id)->first();
    expect($recipient->read_at)->not->toBeNull();
});

it('blocks a tenant from viewing an announcement for someone else', function () {
    $announcement = app(AnnouncementService::class)->create(['title' => 'B', 'body' => 'x', 'target_type' => AnnouncementTarget::Room, 'target_id' => $this->roomB->id]);
    app(AnnouncementService::class)->publish($announcement);

    $this->actingAs($this->userA)
        ->get(route('tenant.announcements.show', $announcement))
        ->assertNotFound();
});

it('archives an announcement', function () {
    $announcement = app(AnnouncementService::class)->create(['title' => 'Arsip', 'body' => 'x', 'target_type' => AnnouncementTarget::All]);
    app(AnnouncementService::class)->publish($announcement);
    app(AnnouncementService::class)->archive($announcement);

    expect($announcement->refresh()->status->value)->toBe('archived');
});

it('forbids tenants from the admin announcement page', function () {
    $this->actingAs($this->userA)
        ->get(route('admin.announcements'))
        ->assertForbidden();
});

it('renders admin announcement pages', function () {
    $announcement = app(AnnouncementService::class)->create(['title' => 'Admin', 'body' => 'x', 'target_type' => AnnouncementTarget::All]);

    $this->actingAs($this->owner)
        ->get(route('admin.announcements'))->assertOk()->assertSee('Pengumuman');
    $this->get(route('admin.announcements.create'))->assertOk();
    $this->get(route('admin.announcements.edit', $announcement))->assertOk();
});
