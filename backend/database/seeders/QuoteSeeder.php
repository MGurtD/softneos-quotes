<?php

namespace Database\Seeders;

use App\Enums\QuoteStatus;
use App\Models\Customer;
use App\Models\ProductReference;
use App\Models\Quote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 15 quotes P-2026-0001..0015 in mixed states, each with 2-6 lines whose
 * description, price and VAT are snapshots of the catalogue reference.
 */
class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $references = ProductReference::with('vatType')->get();

        $statuses = [
            QuoteStatus::Accepted, QuoteStatus::Accepted, QuoteStatus::Rejected,
            QuoteStatus::Accepted, QuoteStatus::Sent, QuoteStatus::Rejected,
            QuoteStatus::Accepted, QuoteStatus::Sent, QuoteStatus::Sent,
            QuoteStatus::Accepted, QuoteStatus::Sent, QuoteStatus::Draft,
            QuoteStatus::Sent, QuoteStatus::Draft, QuoteStatus::Draft,
        ];

        $issueDate = Carbon::create(2026, 9, 1);

        foreach ($statuses as $index => $status) {
            $issueDate = $issueDate->copy()->addDays(fake()->numberBetween(2, 7));

            $quote = Quote::factory()->withStatus($status)->create([
                'number' => sprintf('P-2026-%04d', $index + 1),
                'customer_id' => $customers->random()->id,
                'issue_date' => $issueDate,
                'valid_until' => $issueDate->copy()->addDays(30),
                'notes' => fake()->boolean(30) ? 'Termini de lliurament: 3 setmanes des de l’acceptació.' : null,
            ]);

            $lineReferences = $references->random(fake()->numberBetween(2, 6))->values();

            foreach ($lineReferences as $position => $reference) {
                $quote->lines()->create([
                    'position' => $position + 1,
                    'product_reference_id' => $reference->id,
                    'description' => $reference->description,
                    'quantity' => fake()->randomElement(['1', '2', '5', '10', '12.5', '25', '40']),
                    'unit_price' => $reference->unit_price,
                    'discount_pct' => fake()->randomElement(['0', '0', '0', '5', '10']),
                    'vat_type_id' => $reference->vat_type_id,
                    'vat_rate' => $reference->vatType->rate,
                ]);
            }
        }
    }
}
