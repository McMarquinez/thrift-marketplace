<?php

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

Route::get('/cart', function () use ($gcashPaymentDetails) {
    return view('storefront.cart', $gcashPaymentDetails());
})->name('storefront.cart');

Route::get('/order-confirmation', function () use ($gcashPaymentDetails) {
    return view('storefront.order-confirmation', $gcashPaymentDetails());
})->name('storefront.confirmation');

Route::get('/track-order', function () {
    return view('storefront.track-order');
})->name('storefront.track');
