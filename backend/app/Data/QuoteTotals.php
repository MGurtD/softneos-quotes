<?php

namespace App\Data;

/**
 * Totals of a quote. All amounts are 2-decimal strings, never floats.
 */
final readonly class QuoteTotals
{
    /**
     * @param  list<array{rate: string, base: string, vat: string}>  $vatBreakdown
     */
    public function __construct(
        public array $vatBreakdown,
        public string $subtotal,
        public string $vatTotal,
        public string $total,
    ) {}

    /**
     * Shape used by the API ("totals" in QuoteResource).
     *
     * @return array{vat_breakdown: list<array{rate: string, base: string, vat: string}>, subtotal: string, vat_total: string, total: string}
     */
    public function toArray(): array
    {
        return [
            'vat_breakdown' => $this->vatBreakdown,
            'subtotal' => $this->subtotal,
            'vat_total' => $this->vatTotal,
            'total' => $this->total,
        ];
    }
}
