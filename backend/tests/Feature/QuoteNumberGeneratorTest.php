<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Services\QuoteNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Feature test (not unit) because numbering depends on what is in the database.
 */
class QuoteNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbers_are_correlative_within_a_year(): void
    {
        Quote::factory()->create(['number' => 'P-2026-0001']);
        Quote::factory()->create(['number' => 'P-2026-0002']);

        $next = $this->app->make(QuoteNumberGenerator::class)->next(Carbon::create(2026, 11, 17));

        $this->assertSame('P-2026-0003', $next);
    }

    public function test_sequence_restarts_when_the_year_changes(): void
    {
        Quote::factory()->create(['number' => 'P-2026-0041']);

        $next = $this->app->make(QuoteNumberGenerator::class)->next(Carbon::create(2027, 1, 2));

        $this->assertSame('P-2027-0001', $next);
    }
}
