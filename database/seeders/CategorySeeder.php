<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tree = [
            ['name' => 'Electrónica',  'color' => '#6366f1', 'icon' => 'cpu',     'children' => ['Smartphones', 'Laptops', 'Accesorios']],
            ['name' => 'Alimentos',    'color' => '#10b981', 'icon' => 'apple',   'children' => ['Bebidas', 'Snacks', 'Lácteos']],
            ['name' => 'Ropa',         'color' => '#f59e0b', 'icon' => 'shirt',   'children' => ['Hombre', 'Mujer', 'Niños']],
            ['name' => 'Herramientas', 'color' => '#ef4444', 'icon' => 'wrench',  'children' => ['Manuales', 'Eléctricas']],
            ['name' => 'Oficina',      'color' => '#8b5cf6', 'icon' => 'briefcase', 'children' => ['Papelería', 'Mobiliario']],
        ];

        foreach ($tree as $item) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name'      => $item['name'],
                    'color'     => $item['color'],
                    'icon'      => $item['icon'],
                    'is_active' => true,
                ]
            );

            foreach ($item['children'] as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name'      => $childName,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
