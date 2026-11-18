<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Same rules as creating, except tax_id must be unique ignoring this customer.
 */
class UpdateCustomerRequest extends StoreCustomerRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'tax_id' => ['required', 'string', 'max:20', Rule::unique('customers', 'tax_id')->ignore($this->route('customer'))],
        ];
    }
}
