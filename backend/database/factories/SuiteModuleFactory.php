<?php

namespace Database\Factories;

use App\Models\SuiteModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuiteModule>
 */
class SuiteModuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'summary' => fake()->sentence(12),
            'is_new' => false,
            'position' => fake()->numberBetween(1, 100),
        ];
    }

    public function markedAsNew(): static
    {
        return $this->state(fn (array $attributes): array => ['is_new' => true]);
    }
}
