<?php

namespace Database\Seeders;

use App\Models\ProductReference;
use App\Models\VatType;
use Illuminate\Database\Seeder;

/**
 * Catalogue of a metalworking / machining shop.
 */
class ProductReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $vat = VatType::pluck('id', 'name');

        $references = [
            ['MEC-CNC-001', 'Mecanitzat CNC fresat 3 eixos', 'h', '55.00', 'General'],
            ['MEC-CNC-002', 'Mecanitzat CNC tornejat', 'h', '48.00', 'General'],
            ['MEC-PZA-010', 'Brida d’acer S275 Ø150 mecanitzada', 'pza', '38.50', 'General'],
            ['LAS-COR-001', 'Tall làser xapa d’acer fins a 6 mm', 'm', '4.20', 'General'],
            ['LAS-COR-002', 'Tall làser xapa inoxidable fins a 4 mm', 'm', '6.80', 'General'],
            ['PLE-001', 'Plegat de xapa (per plec)', 'pza', '1.90', 'General'],
            ['SOL-TIG-001', 'Soldadura TIG d’inoxidable', 'h', '42.00', 'General'],
            ['MAT-ACE-275', 'Acer S275JR en barra', 'kg', '1.35', 'General'],
            ['TRT-GAL-001', 'Galvanitzat en calent', 'kg', '0.95', 'Reduït'],
            ['ING-DIS-001', 'Hores de disseny CAD/CAM', 'h', '45.00', 'General'],
            ['FOR-OPE-001', 'Formació d’operari a màquina', 'h', '60.00', 'Exempt'],
            ['DOC-MAN-001', 'Manual tècnic imprès', 'pza', '25.00', 'Superreduït'],
        ];

        foreach ($references as [$code, $description, $unit, $price, $vatName]) {
            ProductReference::create([
                'code' => $code,
                'description' => $description,
                'unit' => $unit,
                'unit_price' => $price,
                'vat_type_id' => $vat[$vatName],
                'active' => true,
            ]);
        }
    }
}
