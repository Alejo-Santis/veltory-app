<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\StockMovementsExport;
use App\Models\Product;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function productsExcel(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        return Excel::download(new ProductsExport($filters), 'productos-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function productsPdf(Request $request)
    {
        $query = Product::with(['unit', 'supplier', 'categories'])->latest();

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'ilike', "%{$search}%")->orWhere('sku', 'ilike', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $products = $query->get();

        $pdf = Pdf::loadView('exports.products', compact('products'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('productos-' . now()->format('Y-m-d') . '.pdf');
    }

    public function movementsExcel(Request $request)
    {
        $filters = $request->only(['search', 'type', 'from', 'to']);
        return Excel::download(new StockMovementsExport($filters), 'movimientos-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function movementsPdf(Request $request)
    {
        $query = StockMovement::with(['product', 'user', 'warehouse'])->latest();

        if ($search = $request->input('search')) {
            $query->whereHas('product', fn ($q) => $q->where('name', 'ilike', "%{$search}%")->orWhere('sku', 'ilike', "%{$search}%"));
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $movements = $query->limit(500)->get();

        $pdf = Pdf::loadView('exports.stock-movements', compact('movements'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('movimientos-' . now()->format('Y-m-d') . '.pdf');
    }

    public function reportPdf(Request $request)
    {
        $period = $request->input('period', '30');
        $from   = now()->subDays((int) $period)->startOfDay();

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
                'products.name', 'products.sku', 'products.stock_quantity', 'products.min_stock',
                'units.abbreviation as unit_abbr',
                DB::raw('ABS(SUM(stock_movements.quantity)) as total_salidas'),
                DB::raw('COUNT(stock_movements.id) as num_movimientos'),
            ]);

        $sinMovimiento = Product::whereNotIn('id', function ($q) use ($from) {
                $q->select('product_id')->from('stock_movements')
                  ->where('created_at', '>=', $from)->whereIn('type', ['out', 'loss']);
            })
            ->where('stock_quantity', '>', 0)->whereNull('deleted_at')
            ->orderByDesc('stock_quantity')->limit(20)
            ->get(['name', 'sku', 'stock_quantity']);

        $valorizacion = DB::table('products')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->whereNull('products.deleted_at')
            ->where('products.stock_quantity', '>', 0)
            ->orderByDesc(DB::raw('products.stock_quantity * COALESCE(products.cost_price, 0)'))
            ->limit(20)
            ->get([
                'products.name', 'products.sku', 'products.stock_quantity',
                'products.cost_price', 'products.sale_price',
                'units.abbreviation as unit_abbr',
                DB::raw('products.stock_quantity * COALESCE(products.cost_price, 0) as valor_costo'),
                DB::raw('products.stock_quantity * COALESCE(products.sale_price, 0) as valor_venta'),
            ]);

        $totals = [
            'valor_costo' => $valorizacion->sum('valor_costo'),
            'valor_venta' => $valorizacion->sum('valor_venta'),
            'productos'   => $valorizacion->count(),
            'margen'      => $valorizacion->sum('valor_costo') > 0
                ? round((($valorizacion->sum('valor_venta') - $valorizacion->sum('valor_costo')) / $valorizacion->sum('valor_costo')) * 100, 1)
                : 0,
        ];

        $periodLabel = match($period) {
            '7'   => '7 días',
            '30'  => '30 días',
            '90'  => '90 días',
            '180' => '6 meses',
            '365' => '1 año',
            default => "{$period} días",
        };

        $pdf = Pdf::loadView('exports.report', compact('rotation', 'sinMovimiento', 'valorizacion', 'totals', 'period', 'periodLabel'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
    }
}
