<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Models\Customer;
use App\Models\ProductReference;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\User;
use App\Models\VatType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_401(): void
    {
        $this->getJson('/api/quotes')->assertUnauthorized();
    }

    public function test_it_creates_a_quote_with_two_lines_and_two_vat_rates(): void
    {
        $general = VatType::factory()->rate('General', '21.00')->create();
        $reduced = VatType::factory()->rate('Reducido', '10.00')->create();
        $reference = ProductReference::factory()->create([
            'description' => 'Mecanizado pieza A-200',
            'unit_price' => '45.00',
            'vat_type_id' => $general->id,
        ]);
        $customer = Customer::factory()->create();

        $response = $this->actingAs(User::factory()->create())->postJson('/api/quotes', [
            'customer_id' => $customer->id,
            'issue_date' => '2026-11-17',
            'valid_until' => '2026-12-17',
            'lines' => [
                // Reference line: description, price and VAT type are copied from the catalogue.
                ['product_reference_id' => $reference->id, 'quantity' => '10', 'discount_pct' => '5'],
                // Free-text line.
                ['description' => 'Galvanizado', 'quantity' => '1', 'unit_price' => '200.00', 'vat_type_id' => $reduced->id],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.number', 'P-2026-0001')
            ->assertJsonPath('data.status.value', 'draft')
            ->assertJsonPath('data.lines.0.description', 'Mecanizado pieza A-200')
            ->assertJsonPath('data.lines.0.vat_rate', '21.00')
            ->assertJsonPath('data.lines.0.base', '427.50')
            ->assertJsonPath('data.lines.1.vat_rate', '10.00')
            ->assertJsonPath('data.totals', [
                'vat_breakdown' => [
                    ['rate' => '21.00', 'base' => '427.50', 'vat' => '89.78'],
                    ['rate' => '10.00', 'base' => '200.00', 'vat' => '20.00'],
                ],
                'subtotal' => '627.50',
                'vat_total' => '109.78',
                'total' => '737.28',
            ]);
    }

    public function test_invalid_input_returns_422_with_field_errors(): void
    {
        $this->actingAs(User::factory()->create())->postJson('/api/quotes', [
            'customer_id' => Customer::factory()->create()->id,
            'issue_date' => '2026-11-17',
            'valid_until' => '2026-11-01',
            'lines' => [
                ['description' => 'Pieza', 'quantity' => '0', 'unit_price' => '10', 'vat_type_id' => VatType::factory()->create()->id],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['valid_until', 'lines.0.quantity']);

        $this->assertDatabaseCount('quotes', 0);
    }

    public function test_a_sent_quote_cannot_be_edited(): void
    {
        $quote = Quote::factory()->sent()->create(['notes' => 'Original']);
        $line = QuoteLine::factory()->for($quote)->create();

        $this->actingAs(User::factory()->create())->putJson("/api/quotes/{$quote->id}", [
            'customer_id' => $quote->customer_id,
            'issue_date' => '2026-11-17',
            'valid_until' => '2026-12-17',
            'notes' => 'Changed',
            'lines' => [
                ['description' => 'Otra', 'quantity' => '1', 'unit_price' => '1', 'vat_type_id' => $line->vat_type_id],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertSame('Original', $quote->fresh()->notes);
    }

    public function test_allowed_status_transition_is_applied(): void
    {
        $quote = Quote::factory()->create();
        QuoteLine::factory()->for($quote)->create();

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/quotes/{$quote->id}/status", ['status' => 'sent'])
            ->assertOk()
            ->assertJsonPath('data.status.value', 'sent')
            ->assertJsonPath('data.status.label', 'Enviat')
            ->assertJsonPath('data.status.transitions.*.value', ['draft', 'accepted', 'rejected']);
    }

    public function test_forbidden_status_transition_returns_422(): void
    {
        $quote = Quote::factory()->accepted()->create();

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/quotes/{$quote->id}/status", ['status' => 'draft'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertSame(QuoteStatus::Accepted, $quote->fresh()->status);
    }

    public function test_quotes_can_be_filtered_by_status(): void
    {
        Quote::factory()->count(2)->sent()->create();
        Quote::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson('/api/quotes?status=sent')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status.value', 'sent');
    }
}
