<?php

namespace App\Http\Controllers;

use App\Enums\TypeStockMovement;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Notifications\StockBajoNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'user', 'warehouse'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
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

        $movements = $query->paginate(30)->withQueryString();

        return Inertia::render('StockMovements/Index', [
            'movements'  => $movements,
            'filters'    => $request->only(['search', 'type', 'from', 'to']),
            'types'      => collect(TypeStockMovement::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
            'products'   => Product::active()
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'sku', 'stock_quantity']),
            'warehouses' => Warehouse::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }

    public function store(StoreStockMovementRequest $request, Product $product)
    {
        $data        = $request->validated();
        $type        = TypeStockMovement::from($data['type']);
        $qty         = (int) $data['quantity'];
        $warehouseId = $data['warehouse_id'] ?? null;
        $before      = $product->stock_quantity;

        // Calcular nuevo stock global según tipo
        $after = match ($type) {
            TypeStockMovement::IN         => $before + $qty,
            TypeStockMovement::OUT        => max(0, $before - $qty),
            TypeStockMovement::RETURN     => $before + $qty,
            TypeStockMovement::LOSS       => max(0, $before - $qty),
            TypeStockMovement::ADJUSTMENT => $qty,
        };

        $storedQty = match ($type) {
            TypeStockMovement::IN, TypeStockMovement::RETURN   => $qty,
            TypeStockMovement::OUT, TypeStockMovement::LOSS    => -$qty,
            TypeStockMovement::ADJUSTMENT                      => $after - $before,
        };

        DB::transaction(function () use ($product, $type, $storedQty, $before, $after, $warehouseId, $data) {
            StockMovement::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'warehouse_id'    => $warehouseId,
                'type'            => $type,
                'quantity'        => $storedQty,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'unit_cost'       => $data['unit_cost'] ?? null,
                'reference'       => $data['reference'] ?? null,
                'notes'           => $data['notes'] ?? null,
            ]);

            $product->update(['stock_quantity' => $after, 'updated_by' => auth()->id()]);

            // Sincronizar warehouse_stock si se indicó una bodega
            if ($warehouseId) {
                $stock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'product_id'   => $product->id,
                ]);

                $currentQty = $stock->quantity ?? 0;

                $stock->quantity   = match ($type) {
                    TypeStockMovement::ADJUSTMENT => $after,
                    default                       => max(0, $currentQty + $storedQty),
                };
                $stock->updated_at = now();
                $stock->save();
            }
        });

        // Notificar a admins si el stock cae a nivel bajo o sin stock
        $product->refresh();
        if ($product->min_stock > 0 && $product->stock_quantity <= $product->min_stock) {
            $admins = User::role(['admin', 'manager'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new StockBajoNotification($product));
            }
        }

        return back()->with('success', "Movimiento registrado. Stock actualizado: {$before} → {$after}.");
    }
}
