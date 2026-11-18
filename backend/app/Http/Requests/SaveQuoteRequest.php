<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Body of POST and PUT /api/quotes. Never accepts number, status, vat_rate
 * or totals: the server decides those. Description, price and VAT type may
 * be omitted when the line has a product reference (they are copied from it).
 */
class SaveQuoteRequest extends FormRequest
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
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_reference_id' => ['nullable', 'integer', 'exists:product_references,id'],
            'lines.*.description' => ['required_without:lines.*.product_reference_id', 'nullable', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'decimal:0,3', 'gt:0'],
            'lines.*.unit_price' => ['required_without:lines.*.product_reference_id', 'nullable', 'decimal:0,2', 'min:0'],
            'lines.*.discount_pct' => ['nullable', 'decimal:0,2', 'between:0,100'],
            'lines.*.vat_type_id' => ['required_without:lines.*.product_reference_id', 'nullable', 'integer', 'exists:vat_types,id'],
        ];
    }
}
