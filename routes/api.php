<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;

Route::middleware('verify.token')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('variants', ProductVariantController::class);
    Route::post('/variants/{id}/decrease-stock', [ProductVariantController::class, 'decreaseStock']);
});
