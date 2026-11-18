<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Same rules as creating, except code must be unique ignoring this reference.
 */
class UpdateProductReferenceRequest extends StoreProductReferenceRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'code' => ['required', 'string', 'max:30', Rule::unique('product_references', 'code')->ignore($this->route('product_reference'))],
        ];
    }
}
