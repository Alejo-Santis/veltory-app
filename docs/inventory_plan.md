# 📦 Sistema de Gestión de Inventario

### Stack: Laravel 11 + Inertia.js + Svelte 5

---

## 1. RESUMEN DEL PROYECTO

Sistema web para gestión de inventario de productos con categorías, control de stock, historial de movimientos y búsqueda avanzada. Construido sobre Laravel 11 como backend API/fullstack con Inertia.js como puente y Svelte 5 como frontend reactivo.

---

## 2. STACK TECNOLÓGICO

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | Laravel | 11.x |
| Bridge | Inertia.js (servidor) | 1.x |
| Frontend | Svelte | 5.x |
| Bridge | @inertiajs/svelte | 1.x |
| Base de datos | MySQL / PostgreSQL | 8+ / 15+ |
| CSS | Tailwind CSS | 3.x |
| Build | Vite | 5.x |
| Auth | Laravel Breeze (Inertia + Svelte) | — |

---

## 3. ESQUEMA DE BASE DE DATOS

### 3.1 Diagrama de Relaciones (ERD resumido)

```
users
  └── products (created_by, updated_by)

categories
  ├── categories (parent_id → self-referencing para subcategorías)
  └── products (via product_category pivot)

products
  ├── product_images (1:N)
  ├── product_category (N:M con categories)
  └── stock_movements (1:N)

units
  └── products (unit_id)

suppliers
  └── products (supplier_id, opcional)
```

---

### 3.2 Tablas y Columnas

#### `users` (generada por Laravel Breeze)

```sql
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
name                VARCHAR(255)
email               VARCHAR(255) UNIQUE
email_verified_at   TIMESTAMP NULL
password            VARCHAR(255)
remember_token      VARCHAR(100) NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

#### `categories`

```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
parent_id       BIGINT UNSIGNED NULL FK → categories.id   -- subcategorías
name            VARCHAR(150) NOT NULL
slug            VARCHAR(150) UNIQUE NOT NULL
description     TEXT NULL
color           VARCHAR(7) NULL                            -- hex color p.ej. #FF5733
icon            VARCHAR(50) NULL                           -- nombre de icono
is_active       BOOLEAN DEFAULT TRUE
sort_order      INT UNSIGNED DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP
deleted_at      TIMESTAMP NULL                             -- SoftDeletes
```

**Índices:**

- `INDEX(parent_id)`
- `INDEX(slug)`
- `INDEX(is_active)`

---

#### `units` (unidades de medida)

```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
name            VARCHAR(50) NOT NULL    -- "Kilogramo", "Litro", "Pieza"
abbreviation    VARCHAR(10) NOT NULL    -- "kg", "L", "pza"
type            ENUM('weight','volume','length','unit','other') DEFAULT 'unit'
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

#### `suppliers` (proveedores, opcional pero recomendado)

```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
name            VARCHAR(200) NOT NULL
contact_name    VARCHAR(150) NULL
email           VARCHAR(255) NULL
phone           VARCHAR(30) NULL
address         TEXT NULL
notes           TEXT NULL
is_active       BOOLEAN DEFAULT TRUE
created_at      TIMESTAMP
updated_at      TIMESTAMP
deleted_at      TIMESTAMP NULL
```

---

#### `products` (tabla principal)

```sql
id                  BIGINT UNSIGNED PK AUTO_INCREMENT
sku                 VARCHAR(100) UNIQUE NOT NULL           -- código único
barcode             VARCHAR(100) UNIQUE NULL               -- código de barras
name                VARCHAR(255) NOT NULL
slug                VARCHAR(255) UNIQUE NOT NULL
description         TEXT NULL
short_description   VARCHAR(500) NULL
unit_id             BIGINT UNSIGNED NULL FK → units.id
supplier_id         BIGINT UNSIGNED NULL FK → suppliers.id
cost_price          DECIMAL(12,2) DEFAULT 0.00            -- precio de costo
sale_price          DECIMAL(12,2) NOT NULL DEFAULT 0.00   -- precio de venta
compare_price       DECIMAL(12,2) NULL                    -- precio comparativo/tachado
tax_rate            DECIMAL(5,2) DEFAULT 0.00             -- % de impuesto
stock_quantity      INT DEFAULT 0                         -- stock actual
min_stock           INT DEFAULT 0                         -- stock mínimo (alerta)
max_stock           INT NULL                              -- stock máximo
track_stock         BOOLEAN DEFAULT TRUE                  -- ¿controlar stock?
allow_backorder     BOOLEAN DEFAULT FALSE                 -- ¿vender sin stock?
weight              DECIMAL(8,3) NULL                     -- peso en kg
dimensions_length   DECIMAL(8,2) NULL
dimensions_width    DECIMAL(8,2) NULL
dimensions_height   DECIMAL(8,2) NULL
status              ENUM('active','inactive','draft','archived') DEFAULT 'active'
featured            BOOLEAN DEFAULT FALSE
notes               TEXT NULL
created_by          BIGINT UNSIGNED NULL FK → users.id
updated_by          BIGINT UNSIGNED NULL FK → users.id
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULL                        -- SoftDeletes
```

**Índices:**

- `UNIQUE(sku)`
- `UNIQUE(barcode)` — puede ser null
- `INDEX(status)`
- `INDEX(supplier_id)`
- `INDEX(unit_id)`
- `INDEX(stock_quantity)`
- `FULLTEXT(name, description)` — para búsqueda

---

#### `product_category` (pivot N:M)

```sql
product_id      BIGINT UNSIGNED FK → products.id
category_id     BIGINT UNSIGNED FK → categories.id
is_primary      BOOLEAN DEFAULT FALSE    -- categoría principal del producto
PRIMARY KEY(product_id, category_id)
```

---

#### `product_images`

```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
product_id      BIGINT UNSIGNED NOT NULL FK → products.id
path            VARCHAR(500) NOT NULL       -- ruta en storage
alt_text        VARCHAR(255) NULL
sort_order      INT UNSIGNED DEFAULT 0
is_cover        BOOLEAN DEFAULT FALSE       -- imagen principal
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

**Índices:**

- `INDEX(product_id)`

---

#### `stock_movements` (historial de movimientos de inventario)

```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
product_id      BIGINT UNSIGNED NOT NULL FK → products.id
user_id         BIGINT UNSIGNED NULL FK → users.id
type            ENUM('in','out','adjustment','return','loss') NOT NULL
quantity        INT NOT NULL               -- positivo o negativo
quantity_before INT NOT NULL               -- stock antes del movimiento
quantity_after  INT NOT NULL               -- stock después del movimiento
unit_cost       DECIMAL(12,2) NULL         -- costo unitario en este movimiento
reference       VARCHAR(100) NULL          -- número de orden, factura, etc.
notes           TEXT NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

**Índices:**

- `INDEX(product_id)`
- `INDEX(type)`
- `INDEX(created_at)`

---

## 4. RELACIONES ELOQUENT

### `Category` Model

```php
// Relación padre-hijo (árbol)
public function parent(): BelongsTo          // → Category
public function children(): HasMany          // → Category[]
public function products(): BelongsToMany    // → Product[] (via product_category)
```

### `Product` Model

```php
public function categories(): BelongsToMany  // → Category[] (via product_category)
public function primaryCategory(): BelongsToMany // → Category (where is_primary=true)
public function unit(): BelongsTo            // → Unit
public function supplier(): BelongsTo        // → Supplier
public function images(): HasMany            // → ProductImage[]
public function coverImage(): HasOne         // → ProductImage (where is_cover=true)
public function stockMovements(): HasMany    // → StockMovement[]
public function createdBy(): BelongsTo       // → User
public function updatedBy(): BelongsTo       // → User

// Accesors útiles
public function getIsLowStockAttribute(): bool
public function getFormattedPriceAttribute(): string
public function getStockStatusAttribute(): string  // 'ok','low','out'
```

### `StockMovement` Model

```php
public function product(): BelongsTo  // → Product
public function user(): BelongsTo     // → User
```

---

## 5. MIGRACIONES (orden de ejecución)

```
2024_01_01_000001_create_units_table.php
2024_01_01_000002_create_suppliers_table.php
2024_01_01_000003_create_categories_table.php
2024_01_01_000004_create_products_table.php
2024_01_01_000005_create_product_category_table.php
2024_01_01_000006_create_product_images_table.php
2024_01_01_000007_create_stock_movements_table.php
```

---

## 6. ESTRUCTURA DE ARCHIVOS DEL PROYECTO

```
inventory-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProductImageController.php
│   │   │   ├── StockMovementController.php
│   │   │   ├── SupplierController.php
│   │   │   └── UnitController.php
│   │   └── Requests/
│   │       ├── StoreCategoryRequest.php
│   │       ├── UpdateCategoryRequest.php
│   │       ├── StoreProductRequest.php
│   │       └── UpdateProductRequest.php
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── ProductImage.php
│   │   ├── StockMovement.php
│   │   ├── Supplier.php
│   │   └── Unit.php
│   └── Services/
│       ├── ProductService.php          -- lógica de negocio de productos
│       └── StockService.php           -- lógica de movimientos de stock
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UnitSeeder.php
│       ├── CategorySeeder.php
│       └── ProductSeeder.php          -- datos de prueba
├── resources/
│   └── js/
│       ├── app.js                     -- entrada Inertia + Svelte
│       ├── Components/
│       │   ├── UI/
│       │   │   ├── Button.svelte
│       │   │   ├── Input.svelte
│       │   │   ├── Badge.svelte
│       │   │   ├── Modal.svelte
│       │   │   ├── Table.svelte
│       │   │   ├── Pagination.svelte
│       │   │   └── Alert.svelte
│       │   └── Inventory/
│       │       ├── ProductCard.svelte
│       │       ├── ProductForm.svelte
│       │       ├── StockBadge.svelte
│       │       ├── CategoryBadge.svelte
│       │       └── StockMovementForm.svelte
│       ├── Layouts/
│       │   ├── AppLayout.svelte       -- layout principal con sidebar
│       │   └── AuthLayout.svelte
│       └── Pages/
│           ├── Auth/
│           │   ├── Login.svelte
│           │   └── Register.svelte
│           ├── Dashboard.svelte       -- resumen con widgets
│           ├── Products/
│           │   ├── Index.svelte       -- listado + búsqueda + filtros
│           │   ├── Create.svelte
│           │   ├── Edit.svelte
│           │   └── Show.svelte        -- detalle del producto
│           ├── Categories/
│           │   ├── Index.svelte
│           │   ├── Create.svelte
│           │   └── Edit.svelte
│           └── Reports/
│               └── StockMovements.svelte
├── routes/
│   └── web.php
└── config/
    └── inventory.php                  -- config específica (alertas, etc.)
```

---

## 7. RUTAS (routes/web.php)

```php
// Autenticación (Breeze genera estas)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Productos
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');

    // Imágenes de productos
    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::delete('product-images/{image}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
    Route::patch('product-images/{image}/cover', [ProductImageController::class, 'setCover'])->name('product-images.cover');

    // Stock
    Route::post('products/{product}/stock', [StockMovementController::class, 'store'])->name('products.stock.store');
    Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');

    // Categorías
    Route::resource('categories', CategoryController::class);

    // Proveedores
    Route::resource('suppliers', SupplierController::class);

    // Unidades
    Route::resource('units', UnitController::class)->only(['index','store','update','destroy']);

});
```

---

## 8. CONTROLLERS — MÉTODOS PRINCIPALES

### `ProductController`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `index` | GET /products | Listado paginado con filtros y búsqueda |
| `create` | GET /products/create | Formulario de creación |
| `store` | POST /products | Guardar nuevo producto |
| `show` | GET /products/{id} | Detalle del producto |
| `edit` | GET /products/{id}/edit | Formulario de edición |
| `update` | PUT /products/{id} | Actualizar producto |
| `destroy` | DELETE /products/{id} | Soft delete |
| `restore` | POST /products/{id}/restore | Restaurar eliminado |

**Filtros en `index`:**

- `?search=texto` — busca en nombre, SKU, descripción
- `?category=id` — filtra por categoría
- `?status=active|inactive|draft|archived`
- `?low_stock=1` — productos bajo stock mínimo
- `?sort=name|price|stock|created_at`
- `?direction=asc|desc`
- `?per_page=15|30|50`

---

## 9. PÁGINAS SVELTE 5 — ESPECIFICACIONES

### `Products/Index.svelte`

**Props recibidas desde Inertia:**

```typescript
{
  products: {
    data: Product[],
    links: PaginationLinks,
    meta: PaginationMeta
  },
  categories: Category[],        // para filtro
  filters: {                     // filtros actuales
    search: string,
    category: number|null,
    status: string,
    low_stock: boolean
  },
  stats: {
    total: number,
    low_stock_count: number,
    out_of_stock_count: number,
    total_value: number
  }
}
```

**Funcionalidades:**

- Tabla con columnas: imagen, SKU, nombre, categorías, precio, stock (con badge de estado), acciones
- Barra de búsqueda con debounce (300ms)
- Filtros en sidebar o dropdowns
- Acciones por fila: Ver, Editar, Eliminar
- Selección múltiple para acciones en masa
- Exportar a CSV (opcional)

---

### `Products/Create.svelte` y `Products/Edit.svelte`

**Secciones del formulario:**

1. **Información básica** — nombre, SKU (auto-generado), descripción corta, descripción larga
2. **Precios** — precio de costo, precio de venta, precio comparativo, tasa de impuesto
3. **Inventario** — cantidad inicial, stock mínimo, stock máximo, unidad, proveedor
4. **Categorías** — selector múltiple con árbol de categorías, indicar categoría principal
5. **Dimensiones** — peso, largo, ancho, alto (opcionales)
6. **Imágenes** — upload múltiple con drag & drop, establecer imagen de portada
7. **Estado** — activo/inactivo/borrador/archivado, destacado

---

### `Products/Show.svelte`

**Secciones:**

- Header con imagen, nombre, SKU, badges de estado y stock
- Precio y datos de inventario en cards resumen
- Descripción completa
- Galería de imágenes
- Categorías asignadas
- Tabla de movimientos de stock recientes
- Botón "Ajustar stock" (abre modal)
- Historial completo de cambios

---

### `Categories/Index.svelte`

- Listado en árbol (tree view) mostrando padre → hijos
- Card o tabla con nombre, color/ícono, cantidad de productos, estado
- Acciones: editar, eliminar (solo si no tiene productos asociados o subcategorías)

---

## 10. SEEDERS DE DATOS INICIALES

### `UnitSeeder` — unidades base

```php
['name' => 'Pieza',      'abbreviation' => 'pza',  'type' => 'unit']
['name' => 'Kilogramo',  'abbreviation' => 'kg',   'type' => 'weight']
['name' => 'Gramo',      'abbreviation' => 'g',    'type' => 'weight']
['name' => 'Litro',      'abbreviation' => 'L',    'type' => 'volume']
['name' => 'Mililitro',  'abbreviation' => 'mL',   'type' => 'volume']
['name' => 'Metro',      'abbreviation' => 'm',    'type' => 'length']
['name' => 'Caja',       'abbreviation' => 'caja', 'type' => 'unit']
['name' => 'Paquete',    'abbreviation' => 'paq',  'type' => 'unit']
```

### `CategorySeeder` — categorías de ejemplo

```php
['name' => 'Electrónica',  'children' => ['Smartphones', 'Laptops', 'Accesorios']]
['name' => 'Alimentos',    'children' => ['Bebidas', 'Snacks', 'Lácteos']]
['name' => 'Ropa',         'children' => ['Hombre', 'Mujer', 'Niños']]
['name' => 'Herramientas', 'children' => ['Manuales', 'Eléctricas']]
```

---

## 11. VALIDACIONES (Form Requests)

### `StoreProductRequest`

```php
'sku'           => 'required|string|max:100|unique:products,sku',
'name'          => 'required|string|max:255',
'description'   => 'nullable|string',
'sale_price'    => 'required|numeric|min:0',
'cost_price'    => 'nullable|numeric|min:0',
'stock_quantity'=> 'required|integer|min:0',
'min_stock'     => 'required|integer|min:0',
'unit_id'       => 'nullable|exists:units,id',
'supplier_id'   => 'nullable|exists:suppliers,id',
'categories'    => 'required|array|min:1',
'categories.*'  => 'exists:categories,id',
'status'        => 'required|in:active,inactive,draft,archived',
'images'        => 'nullable|array',
'images.*'      => 'image|max:5120',  // 5MB máx
```

---

## 12. SERVICIOS (Services)

### `ProductService`

```php
public function generateSku(string $name): string
public function createWithCategories(array $data): Product
public function updateWithCategories(Product $product, array $data): Product
public function softDelete(Product $product): void
public function getWithFilters(array $filters): LengthAwarePaginator
public function getDashboardStats(): array
```

### `StockService`

```php
public function addStock(Product $product, int $qty, string $notes, ?string $ref): StockMovement
public function removeStock(Product $product, int $qty, string $notes, ?string $ref): StockMovement
public function adjustStock(Product $product, int $newQty, string $notes): StockMovement
public function getLowStockProducts(): Collection
```

---

## 13. INSTALACIÓN — PASOS PARA CLAUDE CODE

### Paso 1: Crear proyecto Laravel con Breeze + Svelte

```bash
composer create-project laravel/laravel inventory-app
cd inventory-app
composer require laravel/breeze --dev
php artisan breeze:install svelte-ssr   # o svelte si no necesitas SSR

# Instalar dependencias JS
npm install
npm install @inertiajs/svelte svelte@5
```

### Paso 2: Configurar base de datos (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 3: Crear migraciones en orden

```bash
php artisan make:migration create_units_table
php artisan make:migration create_suppliers_table
php artisan make:migration create_categories_table
php artisan make:migration create_products_table
php artisan make:migration create_product_category_table
php artisan make:migration create_product_images_table
php artisan make:migration create_stock_movements_table
```

### Paso 4: Crear modelos con relaciones

```bash
php artisan make:model Unit
php artisan make:model Supplier
php artisan make:model Category
php artisan make:model Product
php artisan make:model ProductImage
php artisan make:model StockMovement
```

### Paso 5: Crear controllers

```bash
php artisan make:controller CategoryController --resource
php artisan make:controller ProductController --resource
php artisan make:controller ProductImageController
php artisan make:controller StockMovementController --resource
php artisan make:controller SupplierController --resource
php artisan make:controller UnitController --resource
```

### Paso 6: Crear Form Requests

```bash
php artisan make:request StoreCategoryRequest
php artisan make:request UpdateCategoryRequest
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest
php artisan make:request StoreStockMovementRequest
```

### Paso 7: Crear Services

```bash
mkdir app/Services
# Crear manualmente: ProductService.php, StockService.php
```

### Paso 8: Ejecutar migraciones y seeders

```bash
php artisan migrate
php artisan db:seed
```

### Paso 9: Configurar storage para imágenes

```bash
php artisan storage:link
```

---

## 14. CONFIGURACIÓN INERTIA + SVELTE 5

### `resources/js/app.js`

```javascript
import { createInertiaApp } from '@inertiajs/svelte'
import { mount } from 'svelte'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.svelte', { eager: true })
    return pages[`./Pages/${name}.svelte`]
  },
  setup({ el, App, props }) {
    mount(App, { target: el, props })
  },
})
```

### `vite.config.js`

```javascript
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { svelte } from '@sveltejs/vite-plugin-svelte'

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/js/app.js'], refresh: true }),
    svelte(),
  ],
})
```

---

## 15. DASHBOARD — WIDGETS PRINCIPALES

El dashboard (`Pages/Dashboard.svelte`) mostrará:

| Widget | Dato |
|--------|------|
| Total de productos | count activos |
| Valor total en inventario | suma(stock × costo) |
| Productos bajo stock mínimo | count con stock < min_stock |
| Sin stock | count con stock = 0 |
| Últimos movimientos | tabla con 10 más recientes |
| Top categorías | gráfico de dona con distribución |
| Productos más recientes | lista de últimos 5 agregados |

---

## 16. CONSIDERACIONES DE SEGURIDAD Y BUENAS PRÁCTICAS

- Todos los endpoints requieren autenticación (`middleware('auth')`)
- Usar `SoftDeletes` en `products`, `categories` y `suppliers` — no eliminar permanentemente
- Validar todas las entradas con `Form Requests`
- Imágenes: validar MIME type en servidor, almacenar en `storage/app/public/products/`
- Generar SKU automáticamente si no se proporciona (patrón: `PRD-YYYYMMDD-XXXX`)
- Los movimientos de stock son inmutables (no se editan, solo se registran nuevos)
- Añadir `$casts` en modelos para tipos correctos (decimales, booleans, enums)
- Usar `scopeActive()`, `scopeLowStock()` en el modelo `Product` para queries limpias
- Paginación en todos los listados (15 por defecto)
- Índices de base de datos en columnas de búsqueda frecuente

---

## 17. FASE DE DESARROLLO SUGERIDA

### Fase 1 — Base (MVP)

1. Instalación y configuración del stack
2. Migraciones y modelos con relaciones
3. Seeders básicos (units, categories)
4. CRUD de Categorías (backend + frontend)
5. CRUD de Productos sin imágenes (backend + frontend)
6. Listado con búsqueda básica

### Fase 2 — Inventario

7. Upload de imágenes de productos
2. Gestión de stock (entrada/salida/ajuste)
3. Historial de movimientos
4. Alertas de stock mínimo en UI

### Fase 3 — UX y Extras

11. Dashboard con estadísticas y gráficas básicas (ver Fase 4)
2. Filtros avanzados y ordenamiento
3. Búsqueda full-text mejorada
4. Proveedores y unidades de medida
5. Toggle dark/light mode

### Fase 4 — Seguridad, Reportes y Auditoría *(pendiente de implementar)*

> Estas funcionalidades se implementarán después de consolidar el CRUD base de todas las entidades.

#### 4.1 Control de roles — `spatie/laravel-permission`

- **Paquete:** `spatie/laravel-permission`
- **Roles iniciales:** `admin`, `manager`, `viewer`
- **Flujo:** al registrarse, el usuario recibe el rol `viewer` por defecto. El `admin` puede elevar roles desde el panel de usuarios.
- **Gates/Policies:** proteger rutas de creación/edición/borrado según rol. Los `viewer` solo leen.
- **Seeder:** crear usuario `admin@veltory.test` con rol `admin` automáticamente.
- **UI:** pantalla de gestión de usuarios con asignación de roles (solo accesible por `admin`).

```php
// Roles y permisos a definir
$roles = ['admin', 'manager', 'viewer'];

$permissions = [
    'products.view', 'products.create', 'products.edit', 'products.delete',
    'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
    'stock.view', 'stock.adjust',
    'reports.view', 'reports.export',
    'users.view', 'users.manage',
    'audit.view',
];
```

#### 4.2 Exportación e importación masiva

- **Paquete:** `maatwebsite/excel` (Laravel Excel) para Excel/CSV
- **Paquete:** `spatie/browsershot` o `barryvdh/laravel-dompdf` para PDF

**Exportaciones disponibles:**
| Entidad | Formatos | Descripción |
|---|---|---|
| Productos | Excel, CSV, PDF | Listado completo con filtros aplicados |
| Movimientos de stock | Excel, PDF | Historial con rango de fechas |
| Inventario valorizado | PDF | Reporte de valor total por categoría |
| Recibos/albaranes | PDF | Documento por movimiento individual |

**Importación masiva (bulk):**
| Entidad | Formato | Observaciones |
|---|---|---|
| Productos | Excel/CSV | Con validación fila a fila, reporte de errores |
| Categorías | Excel/CSV | Respeta jerarquía padre→hijo por nombre |
| Proveedores | Excel/CSV | |

- Descarga de plantilla Excel con columnas y ejemplos incluidos
- Proceso async para imports grandes (queue job)
- Vista de resultado: filas importadas, filas con error, detalle por fila

#### 4.3 Dashboard con gráficas y acciones rápidas

**Gráficas (librería: `chart.js` vía `svelte-chartjs` o `layerchart`):**
- Gráfico de dona — distribución de productos por categoría
- Gráfico de barras — movimientos de stock últimos 30 días (entradas vs salidas)
- Gráfico de línea — evolución del valor de inventario mensual
- Gráfico de barras horizontal — top 10 productos por valor en stock

**Acciones rápidas en dashboard:**
- Botón "Ajustar stock" rápido sin ir al producto
- Botón "Registrar entrada" (recepción de mercancía)
- Acceso directo a productos bajo stock mínimo
- Últimos 5 movimientos de stock

#### 4.4 Audit Log — trazabilidad de acciones

- **Paquete:** `spatie/laravel-activitylog`
- **Objetivo:** registrar quién hizo qué y cuándo en las entidades principales.

**Eventos auditados:**
| Entidad | Acciones |
|---|---|
| Producto | creado, editado, eliminado, stock ajustado |
| Categoría | creada, editada, eliminada |
| Proveedor | creado, editado, eliminado |
| Usuario | login, logout, cambio de rol |
| Importación | archivo importado, filas procesadas |

**UI del audit log:**
- Pantalla `/audit-log` accesible solo por `admin` y `manager`
- Filtros por: entidad, usuario, tipo de acción, rango de fechas
- Exportable a Excel/PDF
- Muestra diff de campos modificados (antes → después)

```php
// Integración en modelos (ejemplo Product)
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'sale_price', 'stock_quantity', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

---

## 18. TECNOLOGÍAS ADICIONALES PLANIFICADAS

| Paquete | Versión | Uso |
|---|---|---|
| `spatie/laravel-permission` | ^6.x | Control de roles y permisos |
| `spatie/laravel-activitylog` | ^4.x | Audit log de acciones |
| `maatwebsite/excel` | ^3.x | Exportación/importación Excel y CSV |
| `barryvdh/laravel-dompdf` | ^2.x | Generación de PDFs (reportes, recibos) |
| `chart.js` + adaptador Svelte | latest | Gráficas en dashboard |

---

*Documento generado para uso con Claude Code — Proyecto: Veltory — Sistema de Gestión de Inventario*
