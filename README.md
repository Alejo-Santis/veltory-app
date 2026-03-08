# Veltory — Sistema de Gestión de Inventario

> Sistema de inventario web completo para pequeñas y medianas empresas. Diseñado para operar de forma independiente, sin necesidad de integración con sistemas POS o facturación electrónica.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel&logoColor=white)
![Svelte](https://img.shields.io/badge/Svelte-5-FF3E00?style=flat&logo=svelte&logoColor=white)
![Inertia](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat&logo=postgresql&logoColor=white)

---

## Tabla de contenido

- [Características](#características)
- [Stack tecnológico](#stack-tecnológico)
- [Requisitos previos](#requisitos-previos)
- [Instalación](#instalación)
- [Módulos](#módulos)
- [Roles y permisos](#roles-y-permisos)
- [Notificaciones](#notificaciones)
- [Exportaciones](#exportaciones)
- [Estructura del proyecto](#estructura-del-proyecto)

---

## Características

- **Inventario multi-bodega** — Control de stock por ubicación física con movimientos entre bodegas
- **Órdenes de compra** — Flujo completo desde borrador hasta recepción, con generación automática de entradas de stock
- **Notificaciones automáticas** — Alertas de stock bajo, traslados y órdenes de compra con campanita en tiempo real
- **Reportes analíticos** — Rotación de stock y valorización de inventario por período configurable
- **Búsqueda global** — Acceso rápido con `Ctrl+K` a cualquier recurso del sistema
- **Exportación** — PDF y Excel para productos, movimientos de stock y órdenes de compra
- **Control de acceso por roles** — Admin, Manager y Viewer con permisos granulares por ruta
- **Auditoría completa** — Registro de quién modificó cada producto y proveedor, con valores anteriores y nuevos
- **Interfaz oscura** — UI moderna y responsiva optimizada para uso intensivo de escritorio

---

## Stack tecnológico

| Capa | Tecnología | Versión |
| ---- | ---------- | ------- |
| Backend | Laravel | 12 |
| Frontend | Svelte | 5 |
| SPA Bridge | Inertia.js | 2 |
| Estilos | Tailwind CSS | 4 |
| Base de datos | PostgreSQL | 16+ |
| Roles y permisos | Spatie Laravel Permission | 7 |
| Auditoría | Spatie Laravel Activitylog | — |
| PDF | Barryvdh DomPDF | — |
| Excel | Maatwebsite Excel | — |
| Toasts | svelte-sonner | — |
| Bundler | Vite | 7 |

---

## Requisitos previos

- PHP **8.2+** con extensiones: `pdo_pgsql`, `bcmath`, `mbstring`, `gd`
- PostgreSQL **14+**
- Node.js **18+** y npm
- Composer **2+**

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/veltory-app.git
cd veltory-app
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=veltory_app_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 4. Migrar la base de datos

```bash
php artisan migrate
```

### 5. Crear usuario administrador

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name'     => 'Administrador',
    'email'    => 'admin@veltory.com',
    'password' => bcrypt('password'),
]);

// Crear roles y asignar
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
foreach (['admin', 'manager', 'viewer'] as $role) {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
}
$user->assignRole('admin');
```

### 6. Enlace de almacenamiento

```bash
php artisan storage:link
```

### 7. Ejecutar en desarrollo

```bash
# Todo en paralelo (recomendado)
composer run dev

# O por separado:
php artisan serve
php artisan queue:work   # Para notificaciones
npm run dev
```

Accede en: **<http://localhost:8000>**

---

## Módulos

### Productos

- CRUD completo con carga de imágenes múltiples y foto de portada
- Control de stock mínimo y máximo con alertas automáticas
- Estados: Activo, Inactivo, Borrador, Archivado
- Asignación a múltiples categorías, proveedor y unidad de medida
- Historial de movimientos por producto con gráfico de estado de stock

### Categorías

- Estructura jerárquica padre / subcategoría
- Color e ícono personalizables por categoría
- Vista detalle con listado paginado de productos asociados

### Proveedores

- CRUD completo con datos de contacto (nombre, email, teléfono, dirección)
- Softdelete — no se pierde el historial de productos o compras

### Unidades de medida

- Gestión inline sin salir de la página
- Tipos: Peso, Volumen, Longitud, Unidad, Otro

### Movimientos de stock

- Tipos: `in` Entrada · `out` Salida · `adjustment` Ajuste · `return` Devolución · `loss` Pérdida
- Filtros por tipo, rango de fechas y producto
- Notificación automática cuando el stock cae al mínimo configurado

### Bodegas

- Gestión de múltiples ubicaciones físicas
- Stock independiente por bodega y por producto

### Traslados entre bodegas

```text
Borrador → Solicitado → Aprobado → Despachado → Completado
                                              ↘ Cancelado
```

- Genera movimientos de salida (origen) y entrada (destino) al completarse
- Notificaciones automáticas en cada transición de estado

### Órdenes de compra

```text
Borrador → Enviada → Recibida (completa)
                  ↘ Parcial → Recibida (completa)
                  ↘ Cancelada
```

- Referencia automática secuencial: `OC-2026-0001`
- Costo unitario se autocompleta desde el precio de costo del producto (editable)
- Al recibir: genera `StockMovement(type: in)` y actualiza stock en bodega de destino
- Soporte para recepciones parciales en múltiples entregas
- Exportación a PDF con formato de documento comercial

### Reportes

- **Rotación de stock** — Top 20 productos con más salidas + listado de inventario estancado
- **Valorización de inventario** — Valor total a costo, valor a precio de venta y margen potencial
- Selector de período: 7 días, 30 días, 90 días, 6 meses, 1 año

### Búsqueda global (`Ctrl+K`)

Busca en tiempo real sobre: Productos (nombre/SKU), Categorías, Proveedores, Bodegas.
Navegación con teclas ↑↓ y Enter. Cierre con Escape.

### Alertas y notificaciones

Ver sección [Notificaciones](#notificaciones) más abajo.

### Gestión de usuarios

- CRUD de usuarios con asignación y revocación de roles
- Solo accesible para administradores

### Auditoría

Registro automático (Spatie Activitylog) de cada cambio en productos y proveedores:
quién lo hizo, cuándo y qué campos cambiaron.

---

## Roles y permisos

| Acción | Viewer | Manager | Admin |
| ------ | :----: | :-----: | :---: |
| Ver dashboard, listados y reportes | ✅ | ✅ | ✅ |
| Búsqueda global | ✅ | ✅ | ✅ |
| Ver notificaciones | ✅ | ✅ | ✅ |
| Crear / editar productos, categorías, proveedores | ❌ | ✅ | ✅ |
| Registrar movimientos de stock | ❌ | ✅ | ✅ |
| Gestionar traslados y órdenes de compra | ❌ | ✅ | ✅ |
| Exportar PDF / Excel | ❌ | ✅ | ✅ |
| Gestionar usuarios y roles | ❌ | ❌ | ✅ |
| Ver log de auditoría | ❌ | ❌ | ✅ |

---

## Notificaciones

Las notificaciones se almacenan en base de datos (tabla `notifications`) y se muestran
en la campanita 🔔 del topbar con badge de conteo de no leídas.

Desde el dropdown se puede:

- Ver el detalle de cada notificación en un modal
- Navegar directamente al recurso relacionado
- Marcar como leída individualmente o todas a la vez
- Acceder al historial completo en `/notifications`

### Triggers automáticos

| Evento | Notificados |
| ------ | ----------- |
| `stock_quantity ≤ min_stock` al registrar un movimiento | Admin + Manager |
| Traslado: solicitado / aprobado / despachado / completado / cancelado | Admin |
| Orden de compra: creada | Admin + Manager |
| Orden de compra: enviada al proveedor | Admin + Manager |
| Orden de compra: recepción parcial registrada | Admin + Manager |
| Orden de compra: recibida completamente | Admin + Manager |
| Orden de compra: cancelada | Admin + Manager |

---

## Exportaciones

| Recurso | Formato | Orientación |
| ------- | ------- | ----------- |
| Productos | Excel `.xlsx` | — |
| Productos | PDF | A4 apaisado |
| Movimientos de stock | Excel `.xlsx` | — |
| Movimientos de stock | PDF | A4 apaisado |
| Orden de compra (individual) | PDF | A4 vertical |
| Órdenes de compra (listado) | PDF | A4 apaisado |

El PDF de orden de compra individual incluye: datos del proveedor, bodega de recepción,
tabla de ítems con cantidades pedidas/recibidas/pendientes con barra de progreso, y totales.

---

## Estructura del proyecto

```text
veltory-app/
├── app/
│   ├── Enums/
│   │   ├── ProductStatus.php
│   │   ├── TypeStockMovement.php
│   │   └── TypesUnits.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ActivityLogController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ExportController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProductImageController.php
│   │   │   ├── PurchaseOrderController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SearchController.php
│   │   │   ├── StockMovementController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── TransferController.php
│   │   │   ├── UnitController.php
│   │   │   ├── UserController.php
│   │   │   └── WarehouseController.php
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/
│   │       ├── StoreCategoryRequest.php
│   │       ├── StoreProductRequest.php
│   │       ├── StorePurchaseOrderRequest.php
│   │       ├── StoreStockMovementRequest.php
│   │       └── ...
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── ProductImage.php
│   │   ├── PurchaseOrder.php
│   │   ├── PurchaseOrderItem.php
│   │   ├── StockMovement.php
│   │   ├── Supplier.php
│   │   ├── Transfer.php
│   │   ├── TransferItem.php
│   │   ├── Unit.php
│   │   ├── User.php
│   │   ├── Warehouse.php
│   │   └── WarehouseStock.php
│   ├── Notifications/
│   │   ├── OrdenCompraNotification.php
│   │   ├── StockBajoNotification.php
│   │   └── TransferActualizadoNotification.php
│   └── Traits/
│       └── HasUuid.php
├── database/
│   └── migrations/          # 21 migraciones ordenadas cronológicamente
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── Breadcrumb.svelte
│   │   │   ├── NotificationBell.svelte
│   │   │   ├── NotificationDetailModal.svelte
│   │   │   ├── SearchModal.svelte
│   │   │   └── VeltoryLogo.svelte
│   │   ├── Layouts/
│   │   │   └── AppLayout.svelte
│   │   └── Pages/
│   │       ├── Auth/
│   │       ├── Categories/
│   │       ├── Dashboard.svelte
│   │       ├── Notifications/
│   │       ├── Products/
│   │       ├── PurchaseOrders/
│   │       ├── Reports/
│   │       ├── StockMovements/
│   │       ├── Suppliers/
│   │       ├── Transfers/
│   │       ├── Units/
│   │       ├── Users/
│   │       └── Warehouses/
│   └── views/
│       ├── app.blade.php
│       └── exports/
│           ├── products.blade.php
│           ├── purchase-order.blade.php
│           ├── purchase-orders.blade.php
│           └── stock-movements.blade.php
└── routes/
    └── web.php
```

---

## Identidad visual

| Elemento | Color |
| -------- | ----- |
| Fondo principal | `slate-950` |
| Cards y sidebar | `slate-900` |
| Acento primario | `indigo-500 / 600` |
| Stock OK | `emerald-400` |
| Stock bajo | `amber-400` |
| Sin stock / error | `red-400` |

---

## Licencia

Este proyecto es de uso privado. Todos los derechos reservados © 2026.

---

Construido con Laravel 12 · Svelte 5 · Inertia.js 2 · PostgreSQL · Tailwind CSS 4
