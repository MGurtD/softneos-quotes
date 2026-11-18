<?php

namespace App\Data;

/**
 * Amounts of a single quote line, as 2-decimal strings.
 */
final readonly class LineAmounts
{
    public function __construct(
        public string $base,
        public string $vat,
    ) {}
}
