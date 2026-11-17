<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\VatType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Default state is a free-text line (no product reference). vat_rate is
 * copied from the related VAT type, as the application does.
 *
 * @extends Factory<QuoteLine>
 */
class QuoteLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote_id' => Quote::factory(),
            'position' => 1,
            'product_reference_id' => null,
            'description' => fake()->sentence(4),
            'quantity' => fake()->randomFloat(3, 1, 50),
            'unit_price' => fake()->randomFloat(2, 1, 500),
            'discount_pct' => '0.00',
            'vat_type_id' => VatType::factory(),
            'vat_rate' => fn (array $attributes): string => VatType::find($attributes['vat_type_id'])->rate,
        ];
    }
}
