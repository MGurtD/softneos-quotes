<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuoteCalculator;
use Illuminate\Contracts\View\View;

/**
 * Printable A4 view of a quote. Uses the same QuoteCalculator as the API,
 * so the printed totals always match what Angular shows.
 */
class QuotePrintController extends Controller
{
    public function __invoke(Quote $quote, QuoteCalculator $calculator): View
    {
        $quote->load(['customer', 'lines']);

        return view('quotes.print', [
            'quote' => $quote,
            'lines' => $quote->lines->map(fn ($line) => [
                'line' => $line,
                'amounts' => $calculator->line($line),
            ]),
            'totals' => $calculator->totals($quote->lines),
        ]);
    }
}
