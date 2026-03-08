<?php

namespace App\Http\Controllers;

use App\Enums\TypeStockMovement;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Notifications\OrdenCompraNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse'])
            ->withCount('items')
            ->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', fn ($s) => $s->where('name', 'ilike', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return Inertia::render('PurchaseOrders/Index', [
            'orders'  => $orders,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('PurchaseOrders/Create', [
            'suppliers'  => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'products'   => Product::active()
                ->with('unit:id,abbreviation')
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'cost_price', 'unit_id']),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $year      = now()->year;
            $count     = PurchaseOrder::withTrashed()->whereYear('created_at', $year)->count() + 1;
            $reference = 'OC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $order = PurchaseOrder::create([
                'reference'    => $reference,
                'supplier_id'  => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'status'       => 'draft',
                'expected_at'  => $data['expected_at'] ?? null,
                'notes'        => $data['notes'] ?? null,
                'created_by'   => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'product_id'       => $item['product_id'],
                    'quantity_ordered'  => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost'        => $item['unit_cost'] ?? null,
                    'notes'            => $item['notes'] ?? null,
                ]);
            }
        });

        $created = PurchaseOrder::with('supplier')->latest()->first();
        User::role(['admin', 'manager'])->each(
            fn ($u) => $u->notify(new OrdenCompraNotification($created, 'creada'))
        );

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Orden de compra creada correctamente.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'createdBy:id,name',
            'items.product.unit:id,abbreviation',
        ]);

        return Inertia::render('PurchaseOrders/Show', [
            'order' => $this->formatOrder($purchaseOrder),
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->canEdit(), 403, 'Solo se pueden editar órdenes en borrador.');

        $purchaseOrder->load('items.product');

        return Inertia::render('PurchaseOrders/Edit', [
            'order'      => $purchaseOrder,
            'suppliers'  => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'products'   => Product::active()
                ->with('unit:id,abbreviation')
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'cost_price', 'unit_id']),
        ]);
    }

    public function update(StorePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->canEdit(), 403);

        $data = $request->validated();

        DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id'  => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'expected_at'  => $data['expected_at'] ?? null,
                'notes'        => $data['notes'] ?? null,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($data['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id'       => $item['product_id'],
                    'quantity_ordered'  => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost'        => $item['unit_cost'] ?? null,
                    'notes'            => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder->uuid)
            ->with('success', 'Orden actualizada correctamente.');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->isDraft(), 403, 'Solo se pueden enviar órdenes en borrador.');

        $purchaseOrder->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        $purchaseOrder->load('supplier');
        User::role(['admin', 'manager'])->each(
            fn ($u) => $u->notify(new OrdenCompraNotification($purchaseOrder, 'enviada'))
        );

        return back()->with('success', "Orden {$purchaseOrder->reference} enviada al proveedor.");
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->canReceive(), 403, 'Esta orden no está en estado de recepción.');

        $request->validate([
            'items'                        => 'required|array',
            'items.*.id'                   => 'required|integer|exists:purchase_order_items,id',
            'items.*.quantity_received'    => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $allReceived = true;
            $anyReceived = false;

            foreach ($request->input('items') as $incoming) {
                $item = $purchaseOrder->items()->findOrFail($incoming['id']);
                $qty  = (int) $incoming['quantity_received'];

                if ($qty <= 0) {
                    if ($item->quantity_received < $item->quantity_ordered) {
                        $allReceived = false;
                    }
                    continue;
                }

                // No exceder la cantidad pendiente
                $pending  = $item->quantity_ordered - $item->quantity_received;
                $qty      = min($qty, $pending);
                $product  = $item->product;
                $before   = $product->stock_quantity;
                $after    = $before + $qty;

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'warehouse_id'    => $purchaseOrder->warehouse_id,
                    'type'            => TypeStockMovement::IN,
                    'quantity'        => $qty,
                    'quantity_before' => $before,
                    'quantity_after'  => $after,
                    'unit_cost'       => $item->unit_cost,
                    'reference'       => $purchaseOrder->reference,
                    'notes'           => "Recepción OC: {$purchaseOrder->reference}",
                ]);

                $product->update(['stock_quantity' => $after]);

                // Sincronizar warehouse_stock
                $warehouseStock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'product_id'   => $product->id,
                ]);
                $warehouseStock->quantity   = ($warehouseStock->quantity ?? 0) + $qty;
                $warehouseStock->updated_at = now();
                $warehouseStock->save();

                $item->increment('quantity_received', $qty);
                $item->refresh();

                $anyReceived = true;

                if ($item->quantity_received < $item->quantity_ordered) {
                    $allReceived = false;
                }
            }

            if ($anyReceived) {
                $newStatus = $allReceived ? 'received' : 'partial';
                $purchaseOrder->update([
                    'status'      => $newStatus,
                    'received_at' => $allReceived ? now() : $purchaseOrder->received_at,
                ]);

                $purchaseOrder->load('supplier');
                $accion = $allReceived ? 'recibida' : 'recibida_parcial';
                User::role(['admin', 'manager'])->each(
                    fn ($u) => $u->notify(new OrdenCompraNotification($purchaseOrder, $accion))
                );
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder->uuid)
            ->with('success', 'Recepción registrada correctamente.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->canCancel(), 403, 'Esta orden no se puede cancelar.');

        $purchaseOrder->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $purchaseOrder->load('supplier');
        User::role(['admin', 'manager'])->each(
            fn ($u) => $u->notify(new OrdenCompraNotification($purchaseOrder, 'cancelada'))
        );

        return back()->with('success', "Orden {$purchaseOrder->reference} cancelada.");
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->isDraft(), 403, 'Solo se pueden eliminar órdenes en borrador.');

        $ref = $purchaseOrder->reference;
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', "Orden {$ref} eliminada.");
    }

    public function pdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier', 'warehouse', 'createdBy:id,name',
            'items.product.unit:id,abbreviation',
        ]);

        $pdf = Pdf::loadView('exports.purchase-order', ['order' => $purchaseOrder])
            ->setPaper('a4', 'portrait');

        $filename = str_replace('/', '-', $purchaseOrder->reference ?? $purchaseOrder->uuid) . '.pdf';

        return $pdf->download($filename);
    }

    public function listPdf(Request $request)
    {
        $query  = PurchaseOrder::with(['supplier', 'warehouse', 'items'])->withCount('items')->latest();
        $status = $request->input('status');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', fn ($s) => $s->where('name', 'ilike', "%{$search}%"));
            });
        }

        $orders = $query->limit(200)->get();

        $pdf = Pdf::loadView('exports.purchase-orders', compact('orders', 'status'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('ordenes-compra-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Helpers ───────────────────────────────────────────────
    private function formatOrder(PurchaseOrder $order): array
    {
        return [
            'id'           => $order->id,
            'uuid'         => $order->uuid,
            'reference'    => $order->reference,
            'status'       => $order->status,
            'expected_at'  => $order->expected_at?->format('d/m/Y'),
            'sent_at'      => $order->sent_at?->format('d/m/Y H:i'),
            'received_at'  => $order->received_at?->format('d/m/Y H:i'),
            'cancelled_at' => $order->cancelled_at?->format('d/m/Y H:i'),
            'notes'        => $order->notes,
            'can_edit'     => $order->canEdit(),
            'can_receive'  => $order->canReceive(),
            'can_cancel'   => $order->canCancel(),
            'supplier'     => ['id' => $order->supplier->id, 'name' => $order->supplier->name],
            'warehouse'    => ['id' => $order->warehouse->id, 'name' => $order->warehouse->name, 'code' => $order->warehouse->code],
            'created_by'   => $order->createdBy?->name,
            'created_at'   => $order->created_at->format('d/m/Y H:i'),
            'items'        => $order->items->map(fn ($item) => [
                'id'                => $item->id,
                'quantity_ordered'  => $item->quantity_ordered,
                'quantity_received' => $item->quantity_received,
                'pending'           => $item->pending_quantity,
                'unit_cost'         => $item->unit_cost,
                'subtotal'          => $item->subtotal,
                'notes'             => $item->notes,
                'product'           => [
                    'id'   => $item->product->id,
                    'uuid' => $item->product->uuid,
                    'name' => $item->product->name,
                    'sku'  => $item->product->sku,
                    'unit' => $item->product->unit?->abbreviation,
                ],
            ])->values(),
            'total' => $order->total,
        ];
    }
}
