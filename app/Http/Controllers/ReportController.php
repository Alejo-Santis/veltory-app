<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = $request->input('period', '30'); // días
        $from   = now()->subDays((int) $period)->startOfDay();

        // ── Rotación de stock ──────────────────────────────────
        // Movimientos de salida por producto en el período
        $rotation = DB::table('stock_movements')
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->where('stock_movements.created_at', '>=', $from)
            ->whereIn('stock_movements.type', ['out', 'loss'])
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.uuid', 'products.name', 'products.sku',
                      'products.stock_quantity', 'products.min_stock', 'units.abbreviation')
            ->orderByDesc('total_salidas')
            ->limit(20)
            ->get([
                'products.uuid',
                'products.name',
                'products.sku',
                'products.stock_quantity',
                'products.min_stock',
                'units.abbreviation as unit_abbr',
                DB::raw('ABS(SUM(stock_movements.quantity)) as total_salidas'),
                DB::raw('COUNT(stock_movements.id) as num_movimientos'),
            ]);

        // Productos sin movimiento de salida en el período (stock muerto)
        $sinMovimiento = Product::whereNotIn('id', function ($q) use ($from) {
                $q->select('product_id')
                  ->from('stock_movements')
                  ->where('created_at', '>=', $from)
                  ->whereIn('type', ['out', 'loss']);
            })
            ->where('stock_quantity', '>', 0)
            ->whereNull('deleted_at')
            ->orderByDesc('stock_quantity')
            ->limit(10)
            ->get(['uuid', 'name', 'sku', 'stock_quantity']);

        // ── Valorización de inventario ─────────────────────────
        $valorizacion = DB::table('products')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->whereNull('products.deleted_at')
            ->where('products.stock_quantity', '>', 0)
            ->orderByDesc(DB::raw('products.stock_quantity * COALESCE(products.cost_price, 0)'))
            ->get([
                'products.uuid',
                'products.name',
                'products.sku',
                'products.stock_quantity',
                'products.cost_price',
                'products.sale_price',
                'units.abbreviation as unit_abbr',
                'suppliers.name as supplier_name',
                DB::raw('products.stock_quantity * COALESCE(products.cost_price, 0) as valor_costo'),
                DB::raw('products.stock_quantity * COALESCE(products.sale_price, 0) as valor_venta'),
            ]);

        $totalValorCosto  = $valorizacion->sum('valor_costo');
        $totalValorVenta  = $valorizacion->sum('valor_venta');
        $totalProductos   = $valorizacion->count();
        $margenTotal      = $totalValorCosto > 0
            ? (($totalValorVenta - $totalValorCosto) / $totalValorCosto) * 100
            : 0;

        return Inertia::render('Reports/Index', [
            'period'          => $period,
            'rotation'        => $rotation,
            'sinMovimiento'   => $sinMovimiento,
            'valorizacion'    => $valorizacion->take(20),   // top 20 para la tabla
            'totals' => [
                'valor_costo'   => $totalValorCosto,
                'valor_venta'   => $totalValorVenta,
                'productos'     => $totalProductos,
                'margen'        => round($margenTotal, 1),
            ],
            'canWrite' => $request->user()->hasAnyRole(['admin', 'manager']),
        ]);
    }
}
