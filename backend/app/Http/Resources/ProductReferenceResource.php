<?php

namespace App\Http\Resources;

use App\Models\ProductReference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductReference
 */
class ProductReferenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'vat_type_id' => $this->vat_type_id,
            'vat_type' => new VatTypeResource($this->whenLoaded('vatType')),
            'active' => $this->active,
        ];
    }
}
