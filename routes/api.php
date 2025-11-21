<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\ModifierController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // Dashboard statistics
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

    // Categories and Modifiers - Read operations (accessible by all authenticated users)
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/modifiers', [ModifierController::class, 'index'])->name('api.modifiers.index');
    
    // Categories Management - Create, Update, Delete (Admin/Manager)
    Route::post('/categories', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.categories.show');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
    
    // Modifiers Management - Create, Update, Delete (Admin/Manager)
    Route::post('/modifiers', [ModifierController::class, 'store'])->name('api.modifiers.store');
    Route::get('/modifiers/{modifier}', [ModifierController::class, 'show'])->name('api.modifiers.show');
    Route::put('/modifiers/{modifier}', [ModifierController::class, 'update'])->name('api.modifiers.update');
    Route::delete('/modifiers/{modifier}', [ModifierController::class, 'destroy'])->name('api.modifiers.destroy');

    // Menu Items - Read operations (accessible by all authenticated users)
    Route::get('/menu-items', [MenuItemController::class, 'index'])->name('api.menu-items.index');
    Route::get('/menu-items/{menuItem}', [MenuItemController::class, 'show'])->name('api.menu-items.show');

    // Menu Items - Write operations (Admin and Manager only)
    Route::middleware('role:Admin,Manager')->group(function () {
        Route::post('/menu-items', [MenuItemController::class, 'store'])->name('api.menu-items.store');
        Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('api.menu-items.update');
        Route::patch('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('api.menu-items.patch');
        Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('api.menu-items.destroy');
    });

    // Order History - Must be BEFORE orders/{order} to avoid route conflict
    Route::get('/orders/history', [OrderHistoryController::class, 'getOrders'])->name('api.order-history.list');
    Route::get('/orders/{id}/details', [OrderHistoryController::class, 'show'])->name('api.order-history.show');

    // Reports - Sales and analytics (Admin/Manager only)
    Route::middleware('role:Admin,Manager')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'getSalesReport'])->name('api.reports.sales');
    });

    // Orders - All authenticated users can manage orders
    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
    Route::get('/orders/active', [OrderController::class, 'active'])->name('api.orders.active');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('api.orders.update');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('api.orders.patch');

    // Order Items - Update status (Admin/Manager only for kitchen display)
    Route::middleware('role:Admin,Manager')->group(function () {
        Route::put('/order-items/{orderItem}/status', [OrderItemController::class, 'updateStatus'])->name('api.order-items.update-status');
    });

    // Payments - Process payments for orders (Cashier/Admin/Manager)
    Route::post('/payments', [PaymentController::class, 'store'])->name('api.payments.store');

    // Tables - View and manage restaurant tables
    Route::get('/tables', [TableController::class, 'index'])->name('api.tables.index');
    Route::post('/tables', [TableController::class, 'store'])->name('api.tables.store');
    Route::get('/tables/{table}', [TableController::class, 'show'])->name('api.tables.show');
    Route::put('/tables/{table}', [TableController::class, 'update'])->name('api.tables.update');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('api.tables.destroy');
});
