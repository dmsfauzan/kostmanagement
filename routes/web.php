<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TenantDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\RoomController as PublicRoomController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\InvoiceController as TenantInvoiceController;
use App\Http\Controllers\Tenant\LeaseController as TenantLeaseController;
use App\Http\Controllers\Tenant\PaymentController as TenantPaymentController;
use App\Livewire\Admin\Amenity\Index as AmenityIndex;
use App\Livewire\Admin\Building\Form as BuildingForm;
use App\Livewire\Admin\Building\Index as BuildingIndex;
use App\Livewire\Admin\Floor\Form as FloorForm;
use App\Livewire\Admin\Floor\Index as FloorIndex;
use App\Livewire\Admin\Invoice\Form as InvoiceForm;
use App\Livewire\Admin\Invoice\Index as InvoiceIndex;
use App\Livewire\Admin\Invoice\Show as InvoiceShow;
use App\Livewire\Admin\Lease\Form as LeaseForm;
use App\Livewire\Admin\Lease\Index as LeaseIndex;
use App\Livewire\Admin\Lease\MoveOut as LeaseMoveOut;
use App\Livewire\Admin\Lease\Show as LeaseShow;
use App\Livewire\Admin\Payment\Index as PaymentIndex;
use App\Livewire\Admin\Payment\Show as PaymentShow;
use App\Livewire\Admin\PaymentMethod\Index as PaymentMethodIndex;
use App\Livewire\Admin\Property\Form as PropertyForm;
use App\Livewire\Admin\Property\Index as PropertyIndex;
use App\Livewire\Admin\Room\Form as RoomForm;
use App\Livewire\Admin\Room\Index as RoomIndex;
use App\Livewire\Admin\Room\Map as RoomMap;
use App\Livewire\Admin\RoomType\Form as RoomTypeForm;
use App\Livewire\Admin\RoomType\Index as RoomTypeIndex;
use App\Livewire\Admin\Tenant\Form as TenantForm;
use App\Livewire\Admin\Tenant\Index as TenantIndex;
use App\Livewire\Admin\Tenant\Show as TenantShow;
use App\Livewire\Tenant\Auth\ActivateAccount;
use App\Livewire\Tenant\Payment\Submit as TenantPaymentSubmit;
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

// Tenant account activation (temporary signed URL from invitation email)
Route::get('tenant/activate/{user}', ActivateAccount::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('tenant.activate');

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
        Route::get('lease', TenantLeaseController::class)->name('lease');
        Route::get('invoices', [TenantInvoiceController::class, 'index'])->name('invoices');
        Route::get('invoices/{invoice}', [TenantInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('payments', [TenantPaymentController::class, 'index'])->name('payments');
        Route::get('payments/create', TenantPaymentSubmit::class)->name('payments.create');
        Route::get('payments/{payment}/proof', PaymentProofController::class)
            ->middleware('can:payment.view')->name('payments.proof');
        Route::get('notifications', function () {
            return view('tenant.notifications');
        })->name('notifications');
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

        // Invoices
        Route::get('invoices', InvoiceIndex::class)
            ->middleware('can:invoice.view')->name('invoices');
        Route::get('invoices/create', InvoiceForm::class)
            ->middleware('can:invoice.create')->name('invoices.create');
        Route::get('invoices/{invoice:invoice_number}', InvoiceShow::class)
            ->middleware('can:invoice.view')->name('invoices.show');

        // Payments
        Route::get('payments', PaymentIndex::class)
            ->middleware('can:payment.view')->name('payments');
        Route::get('payments/{payment}/proof', PaymentProofController::class)
            ->middleware('can:payment.view')->name('payments.proof');
        Route::get('payments/{payment}', PaymentShow::class)
            ->middleware('can:payment.view')->name('payments.show');
        Route::get('payment-methods', PaymentMethodIndex::class)
            ->middleware('can:payment.view')->name('payment-methods');

        // Tenants
        Route::get('tenants', TenantIndex::class)
            ->middleware('can:tenant.view')->name('tenants');
        Route::get('tenants/create', TenantForm::class)
            ->middleware('can:tenant.create')->name('tenants.create');
        Route::get('tenants/{tenant}', TenantShow::class)
            ->middleware('can:tenant.view')->name('tenants.show');
        Route::get('tenants/{tenant}/edit', TenantForm::class)
            ->middleware('can:tenant.update')->name('tenants.edit');
        Route::get('tenant-documents/{document}/download', [TenantDocumentController::class, 'download'])
            ->middleware('can:tenant_document.view')->name('tenant-documents.download');

        // Leases
        Route::get('leases', LeaseIndex::class)
            ->middleware('can:lease.view')->name('leases');
        Route::get('leases/create', LeaseForm::class)
            ->middleware('can:lease.create')->name('leases.create');
        Route::get('leases/{lease}', LeaseShow::class)
            ->middleware('can:lease.view')->name('leases.show');
        Route::get('leases/{lease}/edit', LeaseForm::class)
            ->middleware('can:lease.update')->name('leases.edit');
        Route::get('leases/{lease}/move-out', LeaseMoveOut::class)
            ->middleware('can:lease.terminate')->name('leases.move-out');
    });

require __DIR__.'/auth.php';
