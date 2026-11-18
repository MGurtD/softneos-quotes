<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductReferenceRequest extends FormRequest
{
    /**
     * Authentication is enforced by the auth:sanctum route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:product_references,code'],
            'description' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:10'],
            'unit_price' => ['required', 'decimal:0,2', 'min:0'],
            'vat_type_id' => ['required', 'integer', 'exists:vat_types,id'],
            'active' => ['boolean'],
        ];
    }
}
