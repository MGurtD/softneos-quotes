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
            ['MEC-CNC-001', 'Mecanizado CNC fresado 3 ejes', 'h', '55.00', 'General'],
            ['MEC-CNC-002', 'Mecanizado CNC torneado', 'h', '48.00', 'General'],
            ['MEC-PZA-010', 'Brida de acero S275 Ø150 mecanizada', 'pza', '38.50', 'General'],
            ['LAS-COR-001', 'Corte láser chapa acero hasta 6 mm', 'm', '4.20', 'General'],
            ['LAS-COR-002', 'Corte láser chapa inoxidable hasta 4 mm', 'm', '6.80', 'General'],
            ['PLE-001', 'Plegado de chapa (por pliegue)', 'pza', '1.90', 'General'],
            ['SOL-TIG-001', 'Soldadura TIG inoxidable', 'h', '42.00', 'General'],
            ['MAT-ACE-275', 'Acero S275JR en barra', 'kg', '1.35', 'General'],
            ['TRT-GAL-001', 'Galvanizado en caliente', 'kg', '0.95', 'Reducido'],
            ['ING-DIS-001', 'Horas de diseño CAD/CAM', 'h', '45.00', 'General'],
            ['FOR-OPE-001', 'Formación de operario en máquina', 'h', '60.00', 'Exento'],
            ['DOC-MAN-001', 'Manual técnico impreso', 'pza', '25.00', 'Superreducido'],
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
