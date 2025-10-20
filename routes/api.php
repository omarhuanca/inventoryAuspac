<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\SubBrandController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('/brands', BrandController::class);
Route::apiResource('/sub-brands', SubBrandController::class);

