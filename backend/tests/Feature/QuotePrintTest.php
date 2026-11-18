<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\User;
use App\Models\VatType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotePrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $quote = Quote::factory()->create();

        $this->get("/quotes/{$quote->id}/print")->assertRedirect('/login');
    }

    public function test_it_prints_the_quote_with_its_totals(): void
    {
        $quote = Quote::factory()->create(['number' => 'P-2026-0007']);
        QuoteLine::factory()->for($quote)->create([
            'quantity' => '10',
            'unit_price' => '45.00',
            'discount_pct' => '5',
            'vat_type_id' => VatType::factory()->rate('General', '21.00'),
            'vat_rate' => '21.00',
        ]);

        $this->actingAs(User::factory()->create())
            ->get("/quotes/{$quote->id}/print")
            ->assertOk()
            ->assertSee('P-2026-0007')
            ->assertSee('517,28');
    }
}
