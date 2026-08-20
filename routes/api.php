<?php

use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{product:slug}', [CatalogController::class, 'showProduct']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/brands', [CatalogController::class, 'brands']);
