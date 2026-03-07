<?php

namespace App\Http\Controllers;

use App\Enums\TypesUnits;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('products')->orderBy('name')->get();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'types' => collect(TypesUnits::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    public function store(StoreUnitRequest $request)
    {
        Unit::create($request->validated());

        return back()->with('success', 'Unidad creada correctamente.');
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return back()->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products_count > 0 || $unit->products()->exists()) {
            return back()->with('error', "No se puede eliminar \"{$unit->name}\" porque tiene productos asociados.");
        }

        $unit->delete();

        return back()->with('success', "Unidad \"{$unit->name}\" eliminada correctamente.");
    }
}
