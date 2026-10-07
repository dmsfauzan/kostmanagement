<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\RoomController as PublicRoomController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Livewire\Admin\Amenity\Index as AmenityIndex;
use App\Livewire\Admin\Building\Form as BuildingForm;
use App\Livewire\Admin\Building\Index as BuildingIndex;
use App\Livewire\Admin\Floor\Form as FloorForm;
use App\Livewire\Admin\Floor\Index as FloorIndex;
use App\Livewire\Admin\Property\Form as PropertyForm;
use App\Livewire\Admin\Property\Index as PropertyIndex;
use App\Livewire\Admin\Room\Form as RoomForm;
use App\Livewire\Admin\Room\Index as RoomIndex;
use App\Livewire\Admin\Room\Map as RoomMap;
use App\Livewire\Admin\RoomType\Form as RoomTypeForm;
use App\Livewire\Admin\RoomType\Index as RoomTypeIndex;
/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('rooms', [PublicRoomController::class, 'index'])->name('rooms');
Route::get('rooms/{slug}', [PublicRoomController::class, 'show'])->name('rooms.show');
Route::view('facilities', 'public.facilities')->name('facilities');
Route::view('about', 'public.about')->name('about');
Route::view('rules', 'public.rules')->name('rules');
Route::view('faq', 'public.faq')->name('faq');
Route::view('contact', 'public.contact')->name('contact');

/*
|--------------------------------------------------------------------------
| Shared authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::view('profile', 'profile')->name('profile');
});

/*
|--------------------------------------------------------------------------
| Tenant portal (mobile-first)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:tenant'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('dashboard', TenantDashboardController::class)->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Admin / Owner back-office
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:owner,admin,finance,technician'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');

        // Properties
        Route::get('properties', PropertyIndex::class)
            ->middleware('can:property.view')->name('properties');
        Route::get('properties/create', PropertyForm::class)
            ->middleware('can:property.create')->name('properties.create');
        Route::get('properties/{property}/edit', PropertyForm::class)
            ->middleware('can:property.update')->name('properties.edit');

        // Buildings
        Route::get('buildings', BuildingIndex::class)
            ->middleware('can:building.view')->name('buildings');
        Route::get('buildings/create', BuildingForm::class)
            ->middleware('can:building.create')->name('buildings.create');
        Route::get('buildings/{building}/edit', BuildingForm::class)
            ->middleware('can:building.update')->name('buildings.edit');

        // Floors
        Route::get('floors', FloorIndex::class)
            ->middleware('can:floor.view')->name('floors');
        Route::get('floors/create', FloorForm::class)
            ->middleware('can:floor.create')->name('floors.create');
        Route::get('floors/{floor}/edit', FloorForm::class)
            ->middleware('can:floor.update')->name('floors.edit');

        // Room types
        Route::get('room-types', RoomTypeIndex::class)
            ->middleware('can:room_type.view')->name('room-types');
        Route::get('room-types/create', RoomTypeForm::class)
            ->middleware('can:room_type.create')->name('room-types.create');
        Route::get('room-types/{roomType}/edit', RoomTypeForm::class)
            ->middleware('can:room_type.update')->name('room-types.edit');

        // Amenities
        Route::get('amenities', AmenityIndex::class)
            ->middleware('can:amenity.view')->name('amenities');

        // Rooms
        Route::get('rooms', RoomIndex::class)
            ->middleware('can:room.view')->name('rooms');
        Route::get('rooms/map', RoomMap::class)
            ->middleware('can:room.view')->name('rooms.map');
        Route::get('rooms/create', RoomForm::class)
            ->middleware('can:room.create')->name('rooms.create');
        Route::get('rooms/{room}/edit', RoomForm::class)
            ->middleware('can:room.update')->name('rooms.edit');
    });

require __DIR__.'/auth.php';
