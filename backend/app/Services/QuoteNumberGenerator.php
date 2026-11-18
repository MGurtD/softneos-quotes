<?php

namespace App\Services;

use App\Models\Quote;
use Carbon\CarbonInterface;

/**
 * Assigns P-{year}-{0001}, correlative per year of the issue date.
 *
 * Call it inside the same DB::transaction() that inserts the quote: the
 * read of the last number and the insert must be atomic, or two
 * concurrent requests could get the same number (the unique index on
 * quotes.number is the last line of defence).
 */
class QuoteNumberGenerator
{
    public function next(CarbonInterface $issueDate): string
    {
        $prefix = sprintf('P-%d-', $issueDate->year);

        // Highest existing number, not the count: deleted drafts leave gaps.
        // Zero-padded numbers sort correctly as strings.
        $last = Quote::query()
            ->where('number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->max('number');

        $sequence = $last === null ? 1 : (int) substr($last, strlen($prefix)) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
