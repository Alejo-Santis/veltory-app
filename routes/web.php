<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
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

    // Perfil — cualquier usuario autenticado
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Búsqueda global
    Route::get('/search', SearchController::class)->name('search');

    // Notificaciones
    Route::get('/notifications',            [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Reportes
    Route::get('/reports', ReportController::class)->name('reports.index');

    // Lectura — viewer, manager y admin
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::resource('categories',      CategoryController::class)->only(['index']);
    Route::resource('products',        ProductController::class)->only(['index']);
    Route::resource('suppliers',       SupplierController::class)->only(['index']);
    Route::resource('units',           UnitController::class)->only(['index']);
    Route::resource('warehouses',      WarehouseController::class)->only(['index']);
    // 'show' registrado después de 'create' (en el grupo de escritura) para evitar que {transfer/purchase-order} capture "create"
    Route::resource('transfers',       TransferController::class)->only(['index']);
    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->only(['index'])
        ->parameters(['purchase-orders' => 'purchaseOrder']);

    // Escritura — solo manager y admin
    Route::middleware('role:admin|manager')->group(function () {
        Route::resource('categories', CategoryController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('products',   ProductController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('suppliers',  SupplierController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('units',      UnitController::class)->only(['store', 'update', 'destroy']);
        Route::resource('warehouses', WarehouseController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('transfers',  TransferController::class)->only(['create', 'store']);

        Route::post('/products/{product}/stock', [StockMovementController::class, 'store'])->name('products.stock.store');

        // Imágenes de producto
        Route::post('/products/{product}/images',                      [ProductImageController::class, 'store'])->name('products.images.store');
        Route::delete('/products/{product}/images/{image}',            [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::patch('/products/{product}/images/{image}/cover',       [ProductImageController::class, 'setCover'])->name('products.images.cover');

        Route::patch('transfers/{transfer}/request',  [TransferController::class, 'request'])->name('transfers.request');
        Route::patch('transfers/{transfer}/approve',  [TransferController::class, 'approve'])->name('transfers.approve');
        Route::patch('transfers/{transfer}/ship',     [TransferController::class, 'ship'])->name('transfers.ship');
        Route::patch('transfers/{transfer}/complete', [TransferController::class, 'complete'])->name('transfers.complete');
        Route::patch('transfers/{transfer}/cancel',   [TransferController::class, 'cancel'])->name('transfers.cancel');
    });

    // Órdenes de compra — escritura: manager y admin
    Route::middleware('role:admin|manager')->group(function () {
        Route::resource('purchase-orders', PurchaseOrderController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['purchase-orders' => 'purchaseOrder']);
        Route::patch('purchase-orders/{purchaseOrder}/send',    [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::patch('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::patch('purchase-orders/{purchaseOrder}/cancel',  [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::get('purchase-orders/{purchaseOrder}/pdf',       [PurchaseOrderController::class, 'pdf'])->name('purchase-orders.pdf');
        Route::get('purchase-orders/export/pdf',                [PurchaseOrderController::class, 'listPdf'])->name('purchase-orders.list-pdf');
    });

    // Show routes — registradas DESPUÉS de /*/create para que no las intercepten
    Route::resource('categories',      CategoryController::class)->only(['show']);
    Route::resource('products',        ProductController::class)->only(['show']);
    Route::resource('suppliers',       SupplierController::class)->only(['show']);
    Route::resource('warehouses',      WarehouseController::class)->only(['show']);
    Route::resource('transfers',       TransferController::class)->only(['show']);
    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->only(['show'])
        ->parameters(['purchase-orders' => 'purchaseOrder']);

    // Exportaciones — manager y admin
    Route::middleware('role:admin|manager')->group(function () {
        Route::get('/exports/products/excel',    [ExportController::class, 'productsExcel'])->name('exports.products.excel');
        Route::get('/exports/products/pdf',      [ExportController::class, 'productsPdf'])->name('exports.products.pdf');
        Route::get('/exports/movements/excel',   [ExportController::class, 'movementsExcel'])->name('exports.movements.excel');
        Route::get('/exports/movements/pdf',     [ExportController::class, 'movementsPdf'])->name('exports.movements.pdf');
    });

    // Gestión de usuarios y audit log — solo admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/users',                        [UserController::class, 'index'])->name('users.index');
        Route::post('/users',                       [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',                 [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',              [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/roles',          [UserController::class, 'assignRole'])->name('users.roles.assign');
        Route::delete('/users/{user}/roles/{role}', [UserController::class, 'removeRole'])->name('users.roles.remove');

        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    });
});
