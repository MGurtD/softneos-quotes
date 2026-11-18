<?php

namespace Database\Seeders;

use App\Models\VatType;
use Illuminate\Database\Seeder;

/**
 * The four Spanish VAT rates. Adding a new rate is data, not code.
 */
class VatTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'General', 'rate' => '21.00'],
            ['name' => 'Reduït', 'rate' => '10.00'],
            ['name' => 'Superreduït', 'rate' => '4.00'],
            ['name' => 'Exempt', 'rate' => '0.00'],
        ];

        foreach ($types as $type) {
            VatType::create([...$type, 'active' => true]);
        }
    }
}
