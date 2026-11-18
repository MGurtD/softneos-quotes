<?php

namespace App\Http\Resources;

use App\Models\Quote;
use App\Services\QuoteCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shape of the quote JSON in docs/SPEC.md. Totals are computed here from the
 * lines, never stored.
 *
 * @mixin Quote
 */
class QuoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'issue_date' => $this->issue_date->toDateString(),
            'valid_until' => $this->valid_until->toDateString(),
            'notes' => $this->notes,
            'customer' => $this->whenLoaded('customer', fn (): array => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'tax_id' => $this->customer->tax_id,
            ]),
            'lines' => QuoteLineResource::collection($this->whenLoaded('lines')),
            'totals' => $this->whenLoaded(
                'lines',
                fn (): array => app(QuoteCalculator::class)->totals($this->lines)->toArray(),
            ),
        ];
    }
}
