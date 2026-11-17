<?php

namespace App\Models;

use Database\Factories\QuoteLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * description, unit_price and vat_rate are snapshots copied when the line
 * is created. vat_type_id records which VAT type was chosen; vat_rate
 * freezes the percentage actually applied.
 */
#[Fillable([
    'position',
    'product_reference_id',
    'description',
    'quantity',
    'unit_price',
    'discount_pct',
    'vat_type_id',
    'vat_rate',
])]
class QuoteLine extends Model
{
    /** @use HasFactory<QuoteLineFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount_pct' => 'decimal:2',
            'vat_rate' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Quote, $this>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Optional: free-text lines have no reference.
     *
     * @return BelongsTo<ProductReference, $this>
     */
    public function productReference(): BelongsTo
    {
        return $this->belongsTo(ProductReference::class);
    }

    /**
     * @return BelongsTo<VatType, $this>
     */
    public function vatType(): BelongsTo
    {
        return $this->belongsTo(VatType::class);
    }
}
