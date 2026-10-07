<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');

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
    });

require __DIR__.'/auth.php';
