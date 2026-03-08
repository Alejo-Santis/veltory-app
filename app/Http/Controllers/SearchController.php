<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $products = Product::with('unit')
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('sku',  'ilike', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(6)
            ->get(['uuid', 'name', 'sku', 'stock_quantity', 'status', 'unit_id'])
            ->map(fn ($p) => [
                'type'     => 'product',
                'uuid'     => $p->uuid,
                'label'    => $p->name,
                'sub'      => $p->sku ? "SKU: {$p->sku}" : "Stock: {$p->stock_quantity}",
                'href'     => "/products/{$p->uuid}",
            ]);

        $categories = Category::where('name', 'ilike', "%{$q}%")
            ->orderBy('name')
            ->limit(4)
            ->get(['uuid', 'name', 'color'])
            ->map(fn ($c) => [
                'type'  => 'category',
                'uuid'  => $c->uuid,
                'label' => $c->name,
                'sub'   => 'Categoría',
                'color' => $c->color ?? '#6366f1',
                'href'  => "/categories/{$c->uuid}",
            ]);

        $suppliers = Supplier::where('name', 'ilike', "%{$q}%")
            ->orderBy('name')
            ->limit(4)
            ->get(['uuid', 'name', 'email'])
            ->map(fn ($s) => [
                'type'  => 'supplier',
                'uuid'  => $s->uuid,
                'label' => $s->name,
                'sub'   => $s->email ?? 'Proveedor',
                'href'  => "/suppliers/{$s->uuid}",
            ]);

        $warehouses = Warehouse::where('name', 'ilike', "%{$q}%")
            ->orWhere('code', 'ilike', "%{$q}%")
            ->orderBy('name')
            ->limit(3)
            ->get(['uuid', 'name', 'code'])
            ->map(fn ($w) => [
                'type'  => 'warehouse',
                'uuid'  => $w->uuid,
                'label' => $w->name,
                'sub'   => $w->code ? "[{$w->code}]" : 'Bodega',
                'href'  => "/warehouses/{$w->uuid}",
            ]);

        return response()->json(
            collect([$products, $categories, $suppliers, $warehouses])
                ->flatten(1)
                ->values()
        );
    }
}
