<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderHistoryController;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->name('home');

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/menu', function () {
    return Inertia::render('MenuManagement');
})->middleware(['auth'])->name('menu.index');

Route::get('/modifiers', function () {
    return Inertia::render('ModifierManagement');
})->name('modifiers.index');

Route::get('/categories', function () {
    return Inertia::render('CategoryManagement');
})->name('categories.index');

Route::get('/orders', function () {
    return Inertia::render('OrderTaking');
})->middleware(['auth'])->name('orders.index');

Route::get('/tables', function () {
    return Inertia::render('TableMapView');
})->middleware(['auth'])->name('tables.index');

Route::get('/tables/manage', function () {
    return Inertia::render('TableManagement');
})->middleware(['auth'])->name('tables.manage');

Route::get('/billing', function () {
    return Inertia::render('BillingView');
})->middleware(['auth'])->name('billing.index');

Route::get('/billing/{order}', function ($order) {
    return Inertia::render('BillingView', [
        'orderId' => $order
    ]);
})->middleware(['auth'])->name('billing.show');

Route::get('/kitchen', function () {
    return Inertia::render('KDSView');
})->middleware(['auth'])->name('kitchen.index');

Route::get('/reports', [ReportController::class, 'index'])
    ->middleware(['auth'])->name('reports.index');

Route::get('/order-history', [OrderHistoryController::class, 'index'])
    ->middleware(['auth'])->name('order-history.index');

Route::get('/takeaway', function () {
    return Inertia::render('TakeawayOrders');
})->middleware(['auth'])->name('takeaway.index');

Route::get('/takeaway/new', function () {
    return Inertia::render('TakeawayOrderTaking');
})->middleware(['auth'])->name('takeaway.new');

