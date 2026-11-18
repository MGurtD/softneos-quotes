<?php

namespace Database\Seeders;

use App\Models\SuiteModule;
use Illuminate\Database\Seeder;

/**
 * Modules shown on the corporate page. Editing this content is a data
 * change, not a code change.
 */
class SuiteModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['Producció', 'Ordres de fabricació, fases i seguiment en temps real des del mateix taller.', false],
            ['Costos', 'Cost real de cada peça i de cada comanda: hores de màquina, material i subcontractació.', false],
            ['Fitxatge', 'Registre de jornada i d’operacions per treballador, amb informes a punt per a una inspecció.', false],
            ['Connector universal', 'Integració amb el programari de facturació que ja feu servir, sense dobles entrades.', false],
            ['Pressupostos', 'Pressupostos a partir del catàleg, amb descomptes per línia, desglossament d’IVA i seguiment de l’estat.', true],
        ];

        foreach ($modules as $position => [$name, $summary, $isNew]) {
            SuiteModule::create([
                'name' => $name,
                'summary' => $summary,
                'is_new' => $isNew,
                'position' => $position + 1,
            ]);
        }
    }
}
