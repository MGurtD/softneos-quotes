<?php

namespace App\Models;

use Database\Factories\ProductReferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catalogue item (CNC machining, laser cutting...). Its vat_type is the
 * default VAT proposed when the item is added to a quote line.
 */
#[Fillable(['code', 'description', 'unit', 'unit_price', 'vat_type_id', 'active'])]
class ProductReference extends Model
{
    /** @use HasFactory<ProductReferenceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<VatType, $this>
     */
    public function vatType(): BelongsTo
    {
        return $this->belongsTo(VatType::class);
    }
}
