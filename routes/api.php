<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;

Route::middleware('verify.token')->group(function () {
    Route::apiResource('/product/categories', CategoryController::class);
    Route::apiResource('/product/products', ProductController::class);
    Route::apiResource('/product/variants', ProductVariantController::class);
    Route::post('/product/variants/{id}/decrease-stock', [ProductVariantController::class, 'decreaseStock']);
});
