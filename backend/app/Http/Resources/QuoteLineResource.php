<?php

namespace App\Http\Resources;

use App\Models\QuoteLine;
use App\Services\QuoteCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin QuoteLine
 */
class QuoteLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amounts = app(QuoteCalculator::class)->line($this->resource);

        return [
            'id' => $this->id,
            'position' => $this->position,
            'product_reference_id' => $this->product_reference_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_pct' => $this->discount_pct,
            'vat_type_id' => $this->vat_type_id,
            'vat_rate' => $this->vat_rate,
            'base' => $amounts->base,
            'vat_amount' => $amounts->vat,
        ];
    }
}
