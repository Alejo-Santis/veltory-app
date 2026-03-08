<?php

namespace App\Http\Controllers;

use App\Enums\TransferStatus;
use App\Http\Requests\StoreTransferRequest;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Notifications\TransferActualizadoNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $query = Transfer::with(['fromWarehouse', 'toWarehouse', 'requestedBy'])
            ->withCount('items')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                  ->orWhereHas('fromWarehouse', fn ($w) => $w->where('name', 'ilike', "%{$search}%"))
                  ->orWhereHas('toWarehouse',   fn ($w) => $w->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($warehouse = $request->input('warehouse')) {
            $query->where(function ($q) use ($warehouse) {
                $q->where('from_warehouse_id', $warehouse)
                  ->orWhere('to_warehouse_id', $warehouse);
            });
        }

        $transfers = $query->paginate(20)->withQueryString();

        return Inertia::render('Transfers/Index', [
            'transfers'  => $transfers,
            'filters'    => $request->only(['search', 'status', 'warehouse']),
            'statuses'   => collect(TransferStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
            'warehouses' => Warehouse::active()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Transfers/Create', [
            'warehouses' => Warehouse::active()->orderBy('name')->get(['id', 'uuid', 'name', 'code']),
            'products'   => Product::active()->with('unit')->orderBy('name')
                ->get(['id', 'uuid', 'name', 'sku', 'stock_quantity', 'unit_id']),
        ]);
    }

    public function store(StoreTransferRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $transfer = Transfer::create([
                'reference'          => Transfer::generateReference(),
                'from_warehouse_id'  => $data['from_warehouse_id'],
                'to_warehouse_id'    => $data['to_warehouse_id'],
                'status'             => TransferStatus::DRAFT,
                'requested_by'       => auth()->id(),
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id'         => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                    'notes'              => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('transfers.index')
            ->with('success', 'Traslado creado correctamente.');
    }

    public function show(Transfer $transfer)
    {
        $transfer->load([
            'fromWarehouse', 'toWarehouse',
            'requestedBy', 'approvedBy',
            'items.product.unit',
        ]);

        return Inertia::render('Transfers/Show', [
            'transfer' => $transfer,
            'statuses' => collect(TransferStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
        ]);
    }

    // ── Transiciones de estado ────────────────────────────────

    public function request(Transfer $transfer)
    {
        abort_unless($transfer->canTransitionTo(TransferStatus::REQUESTED), 422, 'Transición no permitida.');

        $transfer->update([
            'status'       => TransferStatus::REQUESTED,
            'requested_at' => now(),
        ]);

        $transfer->load(['fromWarehouse', 'toWarehouse']);
        User::role('admin')->each(fn ($u) => $u->notify(new TransferActualizadoNotification($transfer, 'solicitado')));

        return back()->with('success', 'Traslado enviado para aprobación.');
    }

    public function approve(Transfer $transfer)
    {
        abort_unless($transfer->canTransitionTo(TransferStatus::APPROVED), 422, 'Transición no permitida.');

        $transfer->update([
            'status'      => TransferStatus::APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $transfer->load(['fromWarehouse', 'toWarehouse']);
        User::role(['admin', 'manager'])->each(fn ($u) => $u->notify(new TransferActualizadoNotification($transfer, 'aprobado')));

        return back()->with('success', 'Traslado aprobado.');
    }

    public function ship(Request $request, Transfer $transfer)
    {
        abort_unless($transfer->canTransitionTo(TransferStatus::IN_TRANSIT), 422, 'Transición no permitida.');

        $data = $request->validate([
            'items'                  => ['required', 'array'],
            'items.*.id'             => ['required', 'exists:transfer_items,id'],
            'items.*.quantity_sent'  => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($transfer, $data) {
            foreach ($data['items'] as $item) {
                $transferItem = $transfer->items()->findOrFail($item['id']);
                $qty = (int) $item['quantity_sent'];

                $transferItem->update(['quantity_sent' => $qty]);

                // Descontar stock de la bodega origen
                WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $transfer->from_warehouse_id, 'product_id' => $transferItem->product_id],
                    ['quantity'     => DB::raw("quantity - {$qty}")]
                );
            }

            $transfer->update([
                'status'     => TransferStatus::IN_TRANSIT,
                'shipped_at' => now(),
            ]);
        });

        $transfer->load(['fromWarehouse', 'toWarehouse']);
        User::role(['admin', 'manager'])->each(fn ($u) => $u->notify(new TransferActualizadoNotification($transfer, 'despachado')));

        return back()->with('success', 'Traslado despachado. Stock de bodega origen actualizado.');
    }

    public function complete(Request $request, Transfer $transfer)
    {
        abort_unless($transfer->canTransitionTo(TransferStatus::COMPLETED), 422, 'Transición no permitida.');

        $data = $request->validate([
            'items'                      => ['required', 'array'],
            'items.*.id'                 => ['required', 'exists:transfer_items,id'],
            'items.*.quantity_received'  => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($transfer, $data) {
            foreach ($data['items'] as $item) {
                $transferItem = $transfer->items()->findOrFail($item['id']);
                $qty = (int) $item['quantity_received'];

                $transferItem->update(['quantity_received' => $qty]);

                // Sumar stock en bodega destino
                WarehouseStock::updateOrCreate(
                    ['warehouse_id' => $transfer->to_warehouse_id, 'product_id' => $transferItem->product_id],
                    ['quantity'     => DB::raw("COALESCE(quantity, 0) + {$qty}")]
                );

                // Actualizar stock global del producto (suma de todas las bodegas)
                $totalStock = WarehouseStock::where('product_id', $transferItem->product_id)->sum('quantity');
                $transferItem->product->update(['stock_quantity' => $totalStock]);
            }

            $transfer->update([
                'status'       => TransferStatus::COMPLETED,
                'completed_at' => now(),
            ]);
        });

        $transfer->load(['fromWarehouse', 'toWarehouse']);
        User::role(['admin', 'manager'])->each(fn ($u) => $u->notify(new TransferActualizadoNotification($transfer, 'completado')));

        return back()->with('success', 'Traslado completado. Stock de bodega destino actualizado.');
    }

    public function cancel(Transfer $transfer)
    {
        abort_unless($transfer->canTransitionTo(TransferStatus::CANCELLED), 422, 'Transición no permitida.');

        // Si estaba aprobado, devolver stock reservado (no implementado en este ejemplo
        // porque la reserva es solo lógica — el descuento real ocurre al despachar)

        $transfer->update([
            'status'       => TransferStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Traslado cancelado.');
    }
}
