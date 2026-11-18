<?php

namespace App\Services;

use App\Data\LineAmounts;
use App\Data\QuoteTotals;
use App\Models\QuoteLine;

/**
 * Single source of truth for quote amounts. Uses bcmath (exact decimal
 * arithmetic on strings) and rounds half up to cents at every step:
 *
 *   base = round(quantity * unit_price * (1 - discount_pct / 100))
 *   vat  = round(base * vat_rate / 100)
 */
class QuoteCalculator
{
    /**
     * Internal precision for intermediate results, before rounding to cents.
     */
    private const SCALE = 10;

    public function line(QuoteLine $line): LineAmounts
    {
        $gross = bcmul($line->quantity, $line->unit_price, self::SCALE);
        $discountFactor = bcsub('1', bcdiv($line->discount_pct ?? '0', '100', self::SCALE), self::SCALE);
        $base = $this->roundHalfUp(bcmul($gross, $discountFactor, self::SCALE));

        $vat = $this->roundHalfUp(bcdiv(bcmul($base, $line->vat_rate, self::SCALE), '100', self::SCALE));

        return new LineAmounts($base, $vat);
    }

    /**
     * Totals with the VAT breakdown grouped by rate, highest rate first.
     *
     * @param  iterable<QuoteLine>  $lines
     */
    public function totals(iterable $lines): QuoteTotals
    {
        $groups = [];

        foreach ($lines as $line) {
            $amounts = $this->line($line);
            $rate = $line->vat_rate;

            $groups[$rate] ??= ['rate' => $rate, 'base' => '0.00', 'vat' => '0.00'];
            $groups[$rate]['base'] = bcadd($groups[$rate]['base'], $amounts->base, 2);
            $groups[$rate]['vat'] = bcadd($groups[$rate]['vat'], $amounts->vat, 2);
        }

        krsort($groups, SORT_NUMERIC);

        $subtotal = '0.00';
        $vatTotal = '0.00';

        foreach ($groups as $group) {
            $subtotal = bcadd($subtotal, $group['base'], 2);
            $vatTotal = bcadd($vatTotal, $group['vat'], 2);
        }

        return new QuoteTotals(
            vatBreakdown: array_values($groups),
            subtotal: $subtotal,
            vatTotal: $vatTotal,
            total: bcadd($subtotal, $vatTotal, 2),
        );
    }

    /**
     * Round a decimal string to cents, half up: 89.775 -> 89.78, 89.774 -> 89.77.
     *
     * bcmath functions never round: they truncate to the requested scale.
     */
    private function roundHalfUp(string $value): string
    {
        return bcadd($value, '0.005', 2);
    }
}
