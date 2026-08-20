<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('storefront.index');
})->name('storefront.home');

Route::get('/cart', function () {
    return view('storefront.cart');
})->name('storefront.cart');
