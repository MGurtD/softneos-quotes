<?php

namespace Database\Factories;

use App\Models\VatType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VatType>
 */
class VatTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'General',
            'rate' => '21.00',
            'active' => true,
        ];
    }

    /**
     * A VAT type with the given name and rate.
     */
    public function rate(string $name, string $rate): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => $name,
            'rate' => $rate,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
