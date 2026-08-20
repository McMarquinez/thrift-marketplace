<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('category', 'CategoryCrudController');
    Route::crud('brand', 'BrandCrudController');
    Route::crud('product', 'ProductCrudController');
    Route::crud('customer', 'CustomerCrudController');
    Route::crud('order', 'OrderCrudController');
    Route::post('order/{id}/mark-paid', 'OrderCrudController@markPaid')->name('admin.order.markPaid');
    Route::crud('payment', 'PaymentCrudController');
    Route::crud('shipment', 'ShipmentCrudController');
    Route::crud('setting', 'SettingCrudController');
    Route::crud('product-image', 'ProductImageCrudController');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
