<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductReferenceController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\VatTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * Every API route needs a session: the Angular elements authenticate with the
 * Sanctum cookie (statefulApi in bootstrap/app.php). Route names get the api.
 * prefix so they never clash with the Blade pages (quotes.index...).
 */
Route::middleware('auth:sanctum')->name('api.')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/vat-types', VatTypeController::class);

    Route::apiResource('customers', CustomerController::class)->except('destroy');
    Route::apiResource('product-references', ProductReferenceController::class)->except('destroy');

    Route::apiResource('quotes', QuoteController::class);
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
});
