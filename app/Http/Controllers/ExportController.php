<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\StockMovementsExport;
use App\Models\Product;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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
}
