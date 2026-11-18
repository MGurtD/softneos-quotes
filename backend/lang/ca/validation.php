<?php

/*
 * Catalan messages for the validation rules this application uses. Only
 * these rules are translated on purpose; add a key here when a FormRequest
 * starts using a new rule.
 */
return [
    'after_or_equal' => 'El camp :attribute ha de ser una data igual o posterior a :date.',
    'array' => 'El camp :attribute ha de ser una llista.',
    'between' => [
        'numeric' => 'El camp :attribute ha d’estar entre :min i :max.',
    ],
    'boolean' => 'El camp :attribute ha de ser cert o fals.',
    'date' => 'El camp :attribute no és una data vàlida.',
    'decimal' => 'El camp :attribute ha de tenir :decimal decimals.',
    'email' => 'El camp :attribute ha de ser una adreça de correu vàlida.',
    'enum' => 'El valor seleccionat per a :attribute no és vàlid.',
    'exists' => 'El valor seleccionat per a :attribute no existeix.',
    'gt' => [
        'numeric' => 'El camp :attribute ha de ser més gran que :value.',
    ],
    'integer' => 'El camp :attribute ha de ser un nombre enter.',
    'max' => [
        'numeric' => 'El camp :attribute no pot ser més gran que :max.',
        'string' => 'El camp :attribute no pot tenir més de :max caràcters.',
    ],
    'min' => [
        'array' => 'El camp :attribute ha de tenir almenys :min element.',
        'numeric' => 'El camp :attribute ha de ser com a mínim :min.',
    ],
    'required' => 'El camp :attribute és obligatori.',
    'required_without' => 'El camp :attribute és obligatori quan no s’indica :values.',
    'string' => 'El camp :attribute ha de ser un text.',
    'unique' => 'Aquest :attribute ja està registrat.',

    /*
     * Field names as the user sees them in the forms.
     */
    'attributes' => [
        'name' => 'nom',
        'tax_id' => 'NIF',
        'email' => 'correu electrònic',
        'password' => 'contrasenya',
        'phone' => 'telèfon',
        'address' => 'adreça',
        'city' => 'població',
        'postal_code' => 'codi postal',
        'code' => 'codi',
        'description' => 'descripció',
        'unit' => 'unitat',
        'unit_price' => 'preu unitari',
        'vat_type_id' => 'tipus d’IVA',
        'active' => 'actiu',
        'customer_id' => 'client',
        'issue_date' => 'data d’emissió',
        'valid_until' => 'data de validesa',
        'notes' => 'observacions',
        'status' => 'estat',
        'lines' => 'línies',
        'lines.*.product_reference_id' => 'referència',
        'lines.*.description' => 'descripció de la línia',
        'lines.*.quantity' => 'quantitat',
        'lines.*.unit_price' => 'preu unitari',
        'lines.*.discount_pct' => 'descompte',
        'lines.*.vat_type_id' => 'tipus d’IVA',
    ],
];
