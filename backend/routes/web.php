<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QuotePageController;
use App\Http\Controllers\QuotePrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
});

// Clients area: Blade pages that host the Angular custom elements.
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::view('/pressupostos', 'quotes.index')->name('quotes.index');
    Route::get('/pressupostos/nou', [QuotePageController::class, 'create'])->name('quotes.create');
    Route::get('/pressupostos/{quote}', [QuotePageController::class, 'edit'])->whereNumber('quote')->name('quotes.edit');
    Route::get('/quotes/{quote}/print', QuotePrintController::class)->name('quotes.print');

    Route::view('/clients', 'customers.index')->name('customers.index');
    Route::view('/referencies', 'references.index')->name('references.index');
});
