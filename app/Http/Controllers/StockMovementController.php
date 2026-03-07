<?php

namespace App\Http\Controllers;

use App\Enums\TypeStockMovement;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'user'])
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
            'movements' => $movements,
            'filters'   => $request->only(['search', 'type', 'from', 'to']),
            'types'     => collect(TypeStockMovement::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function store(StoreStockMovementRequest $request, Product $product)
    {
        $data     = $request->validated();
        $type     = TypeStockMovement::from($data['type']);
        $qty      = (int) $data['quantity'];
        $before   = $product->stock_quantity;

        // Calcular nuevo stock según tipo
        $after = match ($type) {
            TypeStockMovement::IN         => $before + $qty,
            TypeStockMovement::OUT        => max(0, $before - $qty),
            TypeStockMovement::RETURN     => $before + $qty,
            TypeStockMovement::LOSS       => max(0, $before - $qty),
            TypeStockMovement::ADJUSTMENT => $qty,   // quantity = valor absoluto final
        };

        // Ajuste: quantity negativa si es salida
        $storedQty = match ($type) {
            TypeStockMovement::IN, TypeStockMovement::RETURN   => $qty,
            TypeStockMovement::OUT, TypeStockMovement::LOSS    => -$qty,
            TypeStockMovement::ADJUSTMENT                      => $after - $before,
        };

        StockMovement::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'type'            => $type,
            'quantity'        => $storedQty,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'unit_cost'       => $data['unit_cost'] ?? null,
            'reference'       => $data['reference'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ]);

        $product->update(['stock_quantity' => $after, 'updated_by' => auth()->id()]);

        return back()->with('success', "Movimiento registrado. Stock actualizado: {$before} → {$after}.");
    }
}
