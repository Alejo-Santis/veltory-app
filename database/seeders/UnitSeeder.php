<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $units = [
            ['name' => 'Pieza',     'abbreviation' => 'pza',  'type' => 'unit'],
            ['name' => 'Kilogramo', 'abbreviation' => 'kg',   'type' => 'weight'],
            ['name' => 'Gramo',     'abbreviation' => 'g',    'type' => 'weight'],
            ['name' => 'Litro',     'abbreviation' => 'L',    'type' => 'volume'],
            ['name' => 'Mililitro', 'abbreviation' => 'mL',   'type' => 'volume'],
            ['name' => 'Metro',     'abbreviation' => 'm',    'type' => 'length'],
            ['name' => 'Caja',      'abbreviation' => 'caja', 'type' => 'unit'],
            ['name' => 'Paquete',   'abbreviation' => 'paq',  'type' => 'unit'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['abbreviation' => $unit['abbreviation']], $unit);
        }
    }
}
