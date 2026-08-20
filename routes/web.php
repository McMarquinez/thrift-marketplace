<?php

use App\Http\Controllers\Auth\StorefrontPasswordResetController;
use App\Http\Controllers\Auth\StorefrontSessionController;
use App\Http\Controllers\Auth\StorefrontUserRegistrationController;
use App\Http\Controllers\StorefrontCheckoutController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

$gcashPaymentDetails = static function (): array {
    $gcashNumber = Setting::query()->where('key', 'gcash_number')->value('value') ?: '09XXXXXXXXX';
    $gcashAccountName = Setting::query()->where('key', 'gcash_account_name')->value('value') ?: 'ThriftMarket';
    $gcashQrUrl = Setting::query()->where('key', 'gcash_qr_url')->value('value');

    return [
        'gcashNumber' => $gcashNumber,
        'gcashAccountName' => $gcashAccountName,
        'gcashQrUrl' => $gcashQrUrl,
    ];
};

Route::get('/', function () {
    return view('storefront.index');
})->name('storefront.home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [StorefrontSessionController::class, 'create'])->name('login');
    Route::post('/login', [StorefrontSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [StorefrontUserRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [StorefrontUserRegistrationController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [StorefrontPasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [StorefrontPasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [StorefrontPasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [StorefrontPasswordResetController::class, 'update'])->name('password.update');
});

Route::post('/logout', [StorefrontSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () use ($gcashPaymentDetails): void {
    Route::get('/cart', function () use ($gcashPaymentDetails) {
        return view('storefront.cart', $gcashPaymentDetails());
    })->name('storefront.cart');

    Route::post('/checkout', [StorefrontCheckoutController::class, 'store'])
        ->name('storefront.checkout');

    Route::get('/order-confirmation', function () use ($gcashPaymentDetails) {
        return view('storefront.order-confirmation', $gcashPaymentDetails());
    })->name('storefront.confirmation');
});

Route::get('/track-order', function () {
    return view('storefront.track-order');
})->name('storefront.track');
