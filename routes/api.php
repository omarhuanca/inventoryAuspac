<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\BundleProductController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\MeasureController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubBrandController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('/brands', BrandController::class);
Route::apiResource('/subbrands', SubBrandController::class);
Route::apiResource('/suppliers', SupplierController::class);
Route::apiResource('/coins', CoinController::class);
Route::apiResource('/measures', MeasureController::class);
Route::apiResource('/products', ProductController::class);
Route::apiResource('/bundles', BundleController::class);
Route::delete('bundleproducts/{bundleId}', [BundleProductController::class, 'destroy']);

