<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Contracts\View\View;

/**
 * Blade pages of the clients area. Each one hosts an Angular element that
 * does the interactive work through /api.
 */
class QuotePageController extends Controller
{
    public function create(): View
    {
        return view('quotes.edit', ['quote' => null]);
    }

    /**
     * Route Model Binding gives a real 404 page for an unknown quote.
     */
    public function edit(Quote $quote): View
    {
        return view('quotes.edit', ['quote' => $quote]);
    }
}
