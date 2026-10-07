<?php

use App\Livewire\Admin\Property\Form;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->owner = User::factory()->withRole('owner')->create();
});

it('renders the property index page for an owner', function () {
    Property::factory()->create(['name' => 'Kost Mawar']);

    $this->actingAs($this->owner)
        ->get(route('admin.properties'))
        ->assertOk()
        ->assertSee('Properti')
        ->assertSee('Kost Mawar');
});

it('creates a property through the livewire form', function () {
    $this->actingAs($this->owner);

    Livewire::test(Form::class)
        ->set('name', 'Kost Melati')
        ->set('city', 'Bandung')
        ->set('status', 'active')
        ->call('save')
        ->assertHasNoErrors();

    expect(Property::query()->where('name', 'Kost Melati')->exists())->toBeTrue();
});

it('validates required fields on the property form', function () {
    $this->actingAs($this->owner);

    Livewire::test(Form::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

it('forbids a technician from creating a property', function () {
    $technician = User::factory()->withRole('technician')->create();

    $this->actingAs($technician)
        ->get(route('admin.properties.create'))
        ->assertForbidden();
});
