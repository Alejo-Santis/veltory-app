<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    // Semillas de Picsum para cada producto (números fijos = imagen consistente)
    private array $picsumSeeds = [
        'laptop'       => ['tech1', 'tech2', 'tech3'],
        'smartphone'   => ['phone1', 'phone2'],
        'tablet'       => ['tablet1', 'tablet2'],
        'monitor'      => ['monitor1', 'monitor2'],
        'teclado'      => ['keyboard1'],
        'mouse'        => ['mouse1'],
        'auriculares'  => ['audio1', 'audio2'],
        'camara'       => ['camera1', 'camera2'],
        'impresora'    => ['printer1'],
        'router'       => ['router1'],
        'disco'        => ['storage1'],
        'memoria'      => ['ram1'],
        'procesador'   => ['cpu1'],
        'gpu'          => ['gpu1', 'gpu2'],
        'fuente'       => ['psu1'],
        'gabinete'     => ['case1', 'case2'],
        'smartwatch'   => ['watch1', 'watch2'],
        'bocina'       => ['speaker1'],
        'proyector'    => ['projector1'],
        'ups'          => ['ups1'],
    ];

    public function run(): void
    {
        $adminUser = User::first();
        $unitPza   = Unit::where('abbreviation', 'pza')->first();

        // Proveedores tech
        $suppliers = $this->createSuppliers();

        // Categorías usadas
        $catElectronica  = Category::where('slug', 'electronica')->first();
        $catSmartphones  = Category::where('slug', 'smartphones')->first();
        $catLaptops      = Category::where('slug', 'laptops')->first();
        $catAccesorios   = Category::where('slug', 'accesorios')->first();

        $products = $this->productData($suppliers, $unitPza, $adminUser);

        foreach ($products as $data) {
            $categoryIds = $data['categories'] ?? [];
            $imageSeeds  = $data['image_seeds'] ?? [];
            unset($data['categories'], $data['image_seeds']);

            // Generar slug único
            $slug = Str::slug($data['name']);
            $base = $slug; $i = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}"; $i++;
            }
            $data['slug'] = $slug;

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                $data
            );

            // Sync categorías
            if (!empty($categoryIds)) {
                $product->categories()->syncWithoutDetaching(
                    collect($categoryIds)->filter()->mapWithKeys(fn ($id, $k) => [$id => ['is_primary' => $k === 0]])->toArray()
                );
            }

            // Descargar y guardar imágenes si el producto no tiene ninguna
            if ($product->images()->count() === 0 && !empty($imageSeeds)) {
                $this->downloadImages($product, $imageSeeds);
            }
        }
    }

    private function createSuppliers(): array
    {
        $data = [
            ['name' => 'TechDistrib S.A.',     'email' => 'ventas@techdistrib.com',  'phone' => '+57 1 234 5678', 'is_active' => true],
            ['name' => 'Importaciones Nexus',   'email' => 'pedidos@nexusimport.com', 'phone' => '+57 4 987 6543', 'is_active' => true],
            ['name' => 'GlobalTech Colombia',   'email' => 'info@globaltech.co',      'phone' => '+57 1 555 0000', 'is_active' => true],
        ];

        return collect($data)->map(fn ($s) =>
            Supplier::firstOrCreate(['email' => $s['email']], $s)
        )->all();
    }

    private function productData(array $suppliers, ?Unit $unit, ?User $admin): array
    {
        $s0 = $suppliers[0]->id;
        $s1 = $suppliers[1]->id;
        $s2 = $suppliers[2]->id;
        $u  = $unit?->id;
        $a  = $admin?->id;

        $catElectronica = Category::where('slug', 'electronica')->value('id');
        $catSmartphones = Category::where('slug', 'smartphones')->value('id');
        $catLaptops     = Category::where('slug', 'laptops')->value('id');
        $catAccesorios  = Category::where('slug', 'accesorios')->value('id');

        return [
            // ── Laptops ────────────────────────────────────────────
            [
                'name' => 'Laptop UltraBook Pro 14"', 'sku' => 'TECH-LPT-001',
                'short_description' => 'Laptop ultradelgada Intel Core i7, 16GB RAM, 512GB SSD',
                'cost_price' => 2_800_000, 'sale_price' => 3_499_000, 'compare_price' => 3_899_000,
                'stock_quantity' => 18, 'min_stock' => 5, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catLaptops, $catElectronica],
                'image_seeds' => ['laptop-pro', 'laptop-open', 'laptop-side'],
            ],
            [
                'name' => 'Laptop Gaming Fury X17', 'sku' => 'TECH-LPT-002',
                'short_description' => 'RTX 4060, Intel i9, 32GB RAM, pantalla 144Hz',
                'cost_price' => 5_200_000, 'sale_price' => 6_399_000,
                'stock_quantity' => 8, 'min_stock' => 3, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s1, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catLaptops, $catElectronica],
                'image_seeds' => ['gaming-laptop', 'gaming-rgb'],
            ],
            [
                'name' => 'MacBook Air M3 13"', 'sku' => 'TECH-LPT-003',
                'short_description' => 'Chip M3, 8GB RAM unificada, 256GB SSD, batería 18h',
                'cost_price' => 4_600_000, 'sale_price' => 5_299_000,
                'stock_quantity' => 12, 'min_stock' => 4, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s2, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catLaptops, $catElectronica],
                'image_seeds' => ['macbook-silver', 'macbook-open'],
            ],

            // ── Smartphones ─────────────────────────────────────────
            [
                'name' => 'Smartphone Galaxy S24 Ultra', 'sku' => 'TECH-PHN-001',
                'short_description' => '6.8" AMOLED, 200MP, S Pen incluido, 512GB',
                'cost_price' => 3_500_000, 'sale_price' => 4_199_000, 'compare_price' => 4_599_000,
                'stock_quantity' => 25, 'min_stock' => 8, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catSmartphones, $catElectronica],
                'image_seeds' => ['galaxy-front', 'galaxy-back', 'galaxy-spen'],
            ],
            [
                'name' => 'iPhone 15 Pro 256GB', 'sku' => 'TECH-PHN-002',
                'short_description' => 'Chip A17 Pro, titanio, cámara 48MP, USB-C',
                'cost_price' => 4_100_000, 'sale_price' => 4_899_000,
                'stock_quantity' => 20, 'min_stock' => 6, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s2, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catSmartphones, $catElectronica],
                'image_seeds' => ['iphone-black', 'iphone-side'],
            ],
            [
                'name' => 'Smartphone Pixel 8 128GB', 'sku' => 'TECH-PHN-003',
                'short_description' => 'Google Tensor G3, IA nativa, 7 años actualizaciones',
                'cost_price' => 2_200_000, 'sale_price' => 2_699_000,
                'stock_quantity' => 4, 'min_stock' => 5, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s1,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catSmartphones],
                'image_seeds' => ['pixel-front', 'pixel-back'],
            ],

            // ── Monitores ──────────────────────────────────────────
            [
                'name' => 'Monitor Curvo 27" QHD 165Hz', 'sku' => 'TECH-MON-001',
                'short_description' => 'Panel VA 1500R, 1ms, FreeSync Premium, HDR400',
                'cost_price' => 890_000, 'sale_price' => 1_149_000,
                'stock_quantity' => 15, 'min_stock' => 4, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios, $catElectronica],
                'image_seeds' => ['monitor-curve', 'monitor-front'],
            ],
            [
                'name' => 'Monitor 4K 32" IPS', 'sku' => 'TECH-MON-002',
                'short_description' => '3840×2160, USB-C 65W, sRGB 99%, altura ajustable',
                'cost_price' => 1_400_000, 'sale_price' => 1_799_000,
                'stock_quantity' => 9, 'min_stock' => 3, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s1,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios, $catElectronica],
                'image_seeds' => ['monitor-4k', 'monitor-4k-side'],
            ],

            // ── Accesorios ─────────────────────────────────────────
            [
                'name' => 'Teclado Mecánico RGB TKL', 'sku' => 'TECH-ACC-001',
                'short_description' => 'Switches Cherry MX Red, 80%, PBT doubleshot, USB-C',
                'cost_price' => 280_000, 'sale_price' => 379_000,
                'stock_quantity' => 30, 'min_stock' => 10, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios],
                'image_seeds' => ['keyboard-mech', 'keyboard-rgb'],
            ],
            [
                'name' => 'Mouse Gamer 25600 DPI', 'sku' => 'TECH-ACC-002',
                'short_description' => 'Sensor óptico, 7 botones programables, peso ajustable',
                'cost_price' => 150_000, 'sale_price' => 219_000,
                'stock_quantity' => 45, 'min_stock' => 15, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios],
                'image_seeds' => ['mouse-gamer'],
            ],
            [
                'name' => 'Auriculares Inalámbricos ANC', 'sku' => 'TECH-AUD-001',
                'short_description' => 'Cancelación activa de ruido, 30h batería, Bluetooth 5.3',
                'cost_price' => 420_000, 'sale_price' => 549_000, 'compare_price' => 649_000,
                'stock_quantity' => 22, 'min_stock' => 7, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s2, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios, $catElectronica],
                'image_seeds' => ['headphones-black', 'headphones-folded'],
            ],
            [
                'name' => 'Cámara Web 4K 60fps', 'sku' => 'TECH-ACC-003',
                'short_description' => 'Lente Sony, HDR, micrófono dual con supresión de ruido',
                'cost_price' => 390_000, 'sale_price' => 489_000,
                'stock_quantity' => 18, 'min_stock' => 5, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s1,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios],
                'image_seeds' => ['webcam-4k'],
            ],

            // ── Almacenamiento ─────────────────────────────────────
            [
                'name' => 'SSD NVMe 1TB Gen4', 'sku' => 'TECH-STO-001',
                'short_description' => '7000 MB/s lectura, PCIe 4.0 x4, M.2 2280, caché DRAM',
                'cost_price' => 280_000, 'sale_price' => 349_000,
                'stock_quantity' => 40, 'min_stock' => 12, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios, $catElectronica],
                'image_seeds' => ['ssd-nvme', 'ssd-top'],
            ],
            [
                'name' => 'Disco Duro Externo 2TB USB-C', 'sku' => 'TECH-STO-002',
                'short_description' => 'Portátil, cifrado AES-256, compatible Time Machine',
                'cost_price' => 190_000, 'sale_price' => 249_000,
                'stock_quantity' => 0, 'min_stock' => 8, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s1,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios],
                'image_seeds' => ['hdd-external'],
            ],

            // ── Redes ──────────────────────────────────────────────
            [
                'name' => 'Router WiFi 6E Tri-Band', 'sku' => 'TECH-NET-001',
                'short_description' => 'AXE7800, 8 streams, cobertura 300m², QoS gaming',
                'cost_price' => 550_000, 'sale_price' => 699_000,
                'stock_quantity' => 11, 'min_stock' => 4, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s2,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catAccesorios, $catElectronica],
                'image_seeds' => ['router-wifi6'],
            ],

            // ── Wearables ──────────────────────────────────────────
            [
                'name' => 'Smartwatch Pro Series 9', 'sku' => 'TECH-WRB-001',
                'short_description' => 'AMOLED always-on, GPS, SpO2, 14 días batería, 5ATM',
                'cost_price' => 480_000, 'sale_price' => 599_000, 'compare_price' => 699_000,
                'stock_quantity' => 16, 'min_stock' => 5, 'status' => ProductStatus::ACTIVE,
                'unit_id' => $u, 'supplier_id' => $s0, 'featured' => true,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catElectronica],
                'image_seeds' => ['smartwatch-black', 'smartwatch-sport'],
            ],

            // ── Producto inactivo (draft) ───────────────────────────
            [
                'name' => 'Tablet Pro 12.9" M4', 'sku' => 'TECH-TAB-001',
                'short_description' => 'Chip M4, pantalla Liquid Retina XDR, Apple Pencil Pro',
                'cost_price' => 4_200_000, 'sale_price' => 4_999_000,
                'stock_quantity' => 0, 'min_stock' => 3, 'status' => ProductStatus::DRAFT,
                'unit_id' => $u, 'supplier_id' => $s2,
                'created_by' => $a, 'updated_by' => $a,
                'categories' => [$catElectronica],
                'image_seeds' => ['tablet-pro'],
            ],
        ];
    }

    private function downloadImages(Product $product, array $seeds): void
    {
        $isFirst = true;

        foreach ($seeds as $i => $seed) {
            try {
                // Usar picsum.photos con seed fija para imagen consistente
                $width = 800; $height = 800;
                $url = "https://picsum.photos/seed/{$seed}/{$width}/{$height}";

                $response = Http::timeout(15)->get($url);

                if (!$response->successful()) continue;

                $folder   = "products/{$product->uuid}";
                $filename = Str::uuid() . '.jpg';
                $path     = "{$folder}/{$filename}";

                Storage::disk('public')->makeDirectory($folder);
                Storage::disk('public')->put($path, $response->body());

                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $path,
                    'alt_text'   => $product->name . ' - imagen ' . ($i + 1),
                    'sort_order' => $i,
                    'is_cover'   => $isFirst,
                ]);

                $isFirst = false;

            } catch (\Throwable $e) {
                // Si falla la descarga de una imagen, continúa con las demás
                $this->command?->warn("  No se pudo descargar imagen '{$seed}' para {$product->name}: {$e->getMessage()}");
            }
        }
    }
}
