<?php

namespace App\Models;

use Database\Factories\VatTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * VAT catalogue (General 21 %, Reducido 10 %...). Rates change by law,
 * so they are managed in a single place.
 */
#[Fillable(['name', 'rate', 'active'])]
#[WithoutTimestamps]
class VatType extends Model
{
    /** @use HasFactory<VatTypeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProductReference, $this>
     */
    public function productReferences(): HasMany
    {
        return $this->hasMany(ProductReference::class);
    }
}
