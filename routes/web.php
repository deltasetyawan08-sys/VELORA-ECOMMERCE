<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');
Route::get('/beranda', HomeController::class)->name('shop.home');

Route::get('/products', [CatalogController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('checkout.buy-now');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:order_number}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order:order_number}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{order:order_number}/items/{orderItem}/rating', [OrderController::class, 'rate'])
        ->middleware('throttle:30,1')->name('orders.items.rate');

    Route::get('/orders/{order:order_number}/payment', [PaymentController::class, 'show'])->name('orders.payment');
    Route::post('/orders/{order:order_number}/payment/token', [PaymentController::class, 'token'])
        ->middleware('throttle:10,1')->name('orders.payment.token');
    Route::post('/orders/{order:order_number}/payment/refresh', [PaymentController::class, 'refresh'])
        ->middleware('throttle:5,1')->name('orders.payment.refresh');
});

Route::post('/midtrans/notification', MidtransWebhookController::class)->name('midtrans.notification');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

    Route::resource('categories', Admin\CategoryController::class)->except(['show']);
    Route::resource('products', Admin\ProductController::class)->except(['show']);

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:order_number}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order:order_number}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order:order_number}/payment/refresh', [Admin\OrderController::class, 'refreshPayment'])
        ->middleware('throttle:10,1')->name('orders.payment.refresh');

    Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
});
