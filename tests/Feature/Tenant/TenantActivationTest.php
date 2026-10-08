<?php

use App\Enums\UserStatus;
use App\Livewire\Tenant\Auth\ActivateAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use App\Services\TenantService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the tenant activation page for a signed URL', function () {
    Notification::fake();

    $owner = User::factory()->withRole('owner')->create();
    $this->actingAs($owner);

    $tenant = Tenant::factory()->create([
        'email' => 'invited@example.test',
        'full_name' => 'Diundang Budi',
        'phone' => '081200000001',
        'status' => 'prospect',
    ]);

    app(TenantService::class)->invite($tenant);
    $user = User::query()->where('email', 'invited@example.test')->firstOrFail();

    Notification::assertSentTo($user, TenantInvitationNotification::class);
    $notification = Notification::sent($user, TenantInvitationNotification::class)->first();
    $url = $notification->toArray($user)['activation_url'];

    $this->get($url)->assertOk()->assertSee('Aktivasi Akun');

    $parsed = parse_url($url);
    parse_str($parsed['query'] ?? '', $qs);
    $unsigned = URL::temporarySignedRoute('tenant.activate', now()->addDays(7), ['user' => $user->id]);
    expect($parsed['path'])->toBe(parse_url($unsigned, PHP_URL_PATH));
});

it('activates a tenant account from the component', function () {
    $tenant = Tenant::factory()->create([
        'email' => 'activate@example.test',
        'full_name' => 'Calon Budi',
        'phone' => '081200000002',
    ]);

    $user = User::factory()->create([
        'email' => 'activate@example.test',
        'name' => 'Calon Budi',
        'password' => 'x',
        'status' => UserStatus::Inactive,
    ]);
    $user->syncRoles(['tenant']);

    $tenant->update(['user_id' => $user->id, 'status' => 'invited']);

    Livewire::test(ActivateAccount::class, ['user' => $user])
        ->set('name', 'Budi Final')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('activate')
        ->assertHasNoErrors();

    $user->refresh();
    $tenant->refresh();

    expect(Hash::check('password', $user->password))->toBeTrue()
        ->and($user->status->value)->toBe('active')
        ->and($tenant->status->value)->toBe('active');
});
