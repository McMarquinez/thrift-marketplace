<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{product:slug}', [CatalogController::class, 'showProduct']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/brands', [CatalogController::class, 'brands']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
Route::post('/orders/{orderNumber}/payment', [OrderController::class, 'payment']);
Route::post('/orders/track', [OrderController::class, 'track']);
Route::post('/payments/webhook', PaymentWebhookController::class);
