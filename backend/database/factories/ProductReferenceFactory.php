<?php

namespace Database\Factories;

use App\Models\ProductReference;
use App\Models\VatType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReference>
 */
class ProductReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('REF-####'),
            'description' => fake()->sentence(3),
            'unit' => fake()->randomElement(['pza', 'kg', 'h', 'm']),
            'unit_price' => fake()->randomFloat(2, 1, 500),
            'vat_type_id' => VatType::factory(),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['active' => false]);
    }
}
