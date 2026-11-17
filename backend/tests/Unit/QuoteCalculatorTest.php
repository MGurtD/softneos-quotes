<?php

namespace Tests\Unit;

use App\Models\QuoteLine;
use App\Services\QuoteCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests: lines are unsaved models, no database involved.
 */
class QuoteCalculatorTest extends TestCase
{
    public function test_line_with_discount_rounds_vat_half_up(): void
    {
        // Spec example: 10 x 45.00 - 5% = 427.50; 21% = 89.775 -> 89.78 (a float gives 89.77).
        $amounts = (new QuoteCalculator)->line($this->line('10', '45.00', '5', '21'));

        $this->assertSame('427.50', $amounts->base);
        $this->assertSame('89.78', $amounts->vat);
    }

    public function test_totals_are_broken_down_by_vat_rate(): void
    {
        $totals = (new QuoteCalculator)->totals([
            $this->line('10', '100.00', '0', '21'),
            $this->line('1', '200.00', '0', '10'),
        ]);

        $this->assertSame([
            ['rate' => '21.00', 'base' => '1000.00', 'vat' => '210.00'],
            ['rate' => '10.00', 'base' => '200.00', 'vat' => '20.00'],
        ], $totals->toArray()['vat_breakdown']);
        $this->assertSame('1200.00', $totals->subtotal);
        $this->assertSame('230.00', $totals->vatTotal);
        $this->assertSame('1430.00', $totals->total);
    }

    public function test_exempt_line_adds_base_but_no_vat(): void
    {
        $totals = (new QuoteCalculator)->totals([
            $this->line('10', '45.00', '5', '21'),
            $this->line('1', '60.00', '0', '0'),
        ]);

        $this->assertSame(['rate' => '0.00', 'base' => '60.00', 'vat' => '0.00'], $totals->toArray()['vat_breakdown'][1]);
        $this->assertSame('487.50', $totals->subtotal);
        $this->assertSame('89.78', $totals->vatTotal);
        $this->assertSame('577.28', $totals->total);
    }

    private function line(string $quantity, string $unitPrice, string $discountPct, string $vatRate): QuoteLine
    {
        return new QuoteLine([
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_pct' => $discountPct,
            'vat_rate' => $vatRate,
        ]);
    }
}
