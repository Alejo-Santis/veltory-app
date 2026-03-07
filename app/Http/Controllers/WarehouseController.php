<?php

namespace App\Http\Controllers;

use App\Enums\TypeWarehouse;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::withCount(['transfersOut', 'transfersIn'])
            ->withSum('stock as total_stock', 'quantity');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $warehouses = $query->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses,
            'filters'    => $request->only(['search', 'type']),
            'types'      => collect(TypeWarehouse::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Warehouses/Create', [
            'types' => collect(TypeWarehouse::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function store(StoreWarehouseRequest $request)
    {
        Warehouse::create($request->validated());

        return redirect()->route('warehouses.index')
            ->with('success', 'Bodega creada correctamente.');
    }

    public function edit(Warehouse $warehouse)
    {
        return Inertia::render('Warehouses/Edit', [
            'warehouse' => $warehouse,
            'types'     => collect(TypeWarehouse::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return redirect()->route('warehouses.index')
            ->with('success', 'Bodega actualizada correctamente.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $hasTransfers = $warehouse->transfersOut()->exists() || $warehouse->transfersIn()->exists();

        if ($hasTransfers) {
            return back()->with('error', 'No se puede eliminar una bodega con traslados registrados.');
        }

        $warehouse->delete();

        return back()->with('success', 'Bodega eliminada correctamente.');
    }
}
