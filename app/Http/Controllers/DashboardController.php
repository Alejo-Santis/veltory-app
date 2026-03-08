<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\Warehouse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_products'     => Product::active()->count(),
            'total_value'        => (float) (Product::active()->selectRaw('SUM(stock_quantity * cost_price) as total')->value('total') ?? 0),
            'low_stock_count'    => Product::active()->lowStock()->count(),
            'out_of_stock_count' => Product::active()->outOfStock()->count(),
            'total_warehouses'   => Warehouse::where('is_active', true)->count(),
            'pending_transfers'  => Transfer::whereIn('status', ['requested', 'approved', 'in_transit'])->count(),
            'total_categories'   => Category::active()->count(),
            'total_suppliers'    => Supplier::where('is_active', true)->count(),
        ];

        // Productos con stock crítico (sin stock o bajo mínimo)
        $criticalStock = Product::active()
            ->with(['unit', 'coverImage'])
            ->where(function ($q) {
                $q->where('stock_quantity', '<=', 0)
                  ->orWhereRaw('min_stock > 0 AND stock_quantity <= min_stock');
            })
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                $coverUrl = null;
                if ($p->coverImage) {
                    $coverUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($p->coverImage->path);
                }
                return [
                    'uuid'           => $p->uuid,
                    'name'           => $p->name,
                    'sku'            => $p->sku,
                    'stock_quantity' => $p->stock_quantity,
                    'min_stock'      => $p->min_stock,
                    'cover_url'      => $coverUrl,
                    'unit_abbr'      => $p->unit?->abbreviation,
                    'status'         => $p->stock_quantity <= 0 ? 'out' : 'low',
                ];
            });

        $recentMovements = StockMovement::with(['product:id,name,sku', 'user:id,name'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($m) => [
                'id'              => $m->id,
                'type'            => $m->type->value,
                'type_label'      => $m->type->label(),
                'quantity'        => $m->quantity,
                'quantity_before' => $m->quantity_before,
                'quantity_after'  => $m->quantity_after,
                'product_name'    => $m->product?->name,
                'product_sku'     => $m->product?->sku,
                'user_name'       => $m->user?->name,
                'created_at'      => $m->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Dashboard', [
            'stats'           => $stats,
            'recentMovements' => $recentMovements,
            'criticalStock'   => $criticalStock,
        ]);
    }
}
