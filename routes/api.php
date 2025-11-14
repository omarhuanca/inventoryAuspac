<?php

use App\Modules\Brand\Controller\BrandController;
use App\Modules\Bundle\Controller\BundleController;
use App\Modules\BundleProduct\Controller\BundleProductController;
use App\Modules\Coin\Controller\CoinController;
use App\Modules\Measure\Controller\MeasureController;
use App\Modules\Product\Controller\ProductController;
use App\Modules\StockBuy\Controller\StockBuyController;
use App\Modules\StockReferral\Controller\StockReferralController;
use App\Modules\SubBrand\Controller\SubBrandController;
use App\Modules\Supplier\Controller\SupplierController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/brands', BrandController::class);
Route::apiResource('/subbrands', SubBrandController::class);
Route::apiResource('/suppliers', SupplierController::class);
Route::apiResource('/coins', CoinController::class);
Route::apiResource('/measures', MeasureController::class);
Route::apiResource('/products', ProductController::class);
Route::apiResource('/bundles', BundleController::class);
Route::delete('bundleproducts/{bundleId}', [BundleProductController::class, 'destroy']);
Route::apiResource('/stockbuys', StockBuyController::class);
Route::apiResource('/stockreferrals', StockReferralController::class);

