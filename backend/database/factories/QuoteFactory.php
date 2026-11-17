<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Customer;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factories run unguarded, so "number" and "status" can be set here even
 * though they are not fillable on the model.
 *
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'number' => 'P-'.$issueDate->format('Y').'-'.fake()->unique()->numerify('####'),
            'customer_id' => Customer::factory(),
            'issue_date' => $issueDate,
            'valid_until' => (clone $issueDate)->modify('+30 days'),
            'status' => QuoteStatus::Draft,
            'notes' => null,
        ];
    }

    public function withStatus(QuoteStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }

    public function sent(): static
    {
        return $this->withStatus(QuoteStatus::Sent);
    }

    public function accepted(): static
    {
        return $this->withStatus(QuoteStatus::Accepted);
    }

    public function rejected(): static
    {
        return $this->withStatus(QuoteStatus::Rejected);
    }
}
