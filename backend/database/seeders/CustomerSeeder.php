<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

/**
 * Fictitious workshops. Tax ids are made up, not real CIFs.
 */
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['Talleres Ebro Mecánica S.L.', 'B50123456', 'Zaragoza', '50014'],
            ['Calderería Hermanos Ruiz S.L.', 'B46234567', 'Valencia', '46980'],
            ['Estampaciones del Vallès S.A.', 'A08345678', 'Granollers', '08400'],
            ['Mecanizados Bilbaína S.L.', 'B48456789', 'Bilbao', '48015'],
            ['Montajes Industriales Tajo S.L.', 'B45567890', 'Toledo', '45007'],
            ['Forja Navarra S.Coop.', 'F31678901', 'Pamplona', '31013'],
            ['Precisión Mecánica Levante S.L.', 'B03789012', 'Alicante', '03114'],
            ['Estructuras Metálicas Sur S.L.', 'B41890123', 'Sevilla', '41016'],
        ];

        foreach ($customers as [$name, $taxId, $city, $postalCode]) {
            Customer::factory()->create([
                'name' => $name,
                'tax_id' => $taxId,
                'city' => $city,
                'postal_code' => $postalCode,
            ]);
        }
    }
}
