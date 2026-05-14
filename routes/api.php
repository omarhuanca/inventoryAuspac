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
use App\Modules\TaxCore\Controller\TaxCoreController;
use App\Modules\Xero\Controller\XeroAuthController;
use App\Modules\Xero\Controller\XeroItemController;
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

Route::prefix('xero')->group(function () {
    Route::get('/auth',     [XeroAuthController::class, 'authorize']);
    Route::get('/callback', [XeroAuthController::class, 'callback']);
    Route::get('/status',   [XeroAuthController::class, 'status']);
    Route::post('/items',   [XeroItemController::class, 'store']);
});

Route::prefix('taxcore')->group(function () {
    Route::get('/status',                   [TaxCoreController::class, 'status']);
    Route::post('/sync-rates',              [TaxCoreController::class, 'syncRates']);
    Route::get('/rates',                    [TaxCoreController::class, 'getRates']);
    Route::post('/fiscalize',               [TaxCoreController::class, 'fiscalize']);
    Route::post('/test-invoice',            [TaxCoreController::class, 'testInvoice']);
    Route::get('/invoices',                 [TaxCoreController::class, 'getInvoices']);
    Route::get('/invoices/{id}',            [TaxCoreController::class, 'getInvoice']);
    Route::post('/invoices/{id}/retry',     [TaxCoreController::class, 'retryInvoice']);
});

