<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Auth — solo para invitados
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login',   [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// App — requiere autenticación
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products',   ProductController::class)->except(['show']);
    Route::resource('suppliers',  SupplierController::class)->except(['show']);
    Route::resource('units',      UnitController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::post('/products/{product}/stock', [StockMovementController::class, 'store'])->name('products.stock.store');

    // Perfil del usuario autenticado
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Gestión de usuarios (solo admin)
    Route::get('/users',                          [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/roles',            [UserController::class, 'assignRole'])->name('users.roles.assign');
    Route::delete('/users/{user}/roles/{role}',   [UserController::class, 'removeRole'])->name('users.roles.remove');

    // Bodegas
    Route::resource('warehouses', WarehouseController::class)->except(['show']);

    // Traslados
    Route::resource('transfers', TransferController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('transfers/{transfer}/request',  [TransferController::class, 'request'])->name('transfers.request');
    Route::patch('transfers/{transfer}/approve',  [TransferController::class, 'approve'])->name('transfers.approve');
    Route::patch('transfers/{transfer}/ship',     [TransferController::class, 'ship'])->name('transfers.ship');
    Route::patch('transfers/{transfer}/complete', [TransferController::class, 'complete'])->name('transfers.complete');
    Route::patch('transfers/{transfer}/cancel',   [TransferController::class, 'cancel'])->name('transfers.cancel');
});
