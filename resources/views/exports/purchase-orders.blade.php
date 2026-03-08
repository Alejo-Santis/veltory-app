<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 20px; }
        h1   { font-size: 17px; color: #4f46e5; margin-bottom: 4px; }
        .meta { color: #64748b; font-size: 9px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        tbody td.right { text-align: right; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 8px; font-weight: bold; }
        .badge-draft     { background: #f1f5f9; color: #475569; }
        .badge-sent      { background: #ede9fe; color: #5b21b6; }
        .badge-partial   { background: #fef3c7; color: #92400e; }
        .badge-received  { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; text-align: right; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Órdenes de Compra</h1>
    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        Total: {{ count($orders) }} ordenes
        @if($status) &nbsp;|&nbsp; Filtro estado: <strong>{{ $status }}</strong> @endif
    </div>

    @php
        $statusLabels = [
            'draft'     => 'Borrador',
            'sent'      => 'Enviada',
            'partial'   => 'Parcial',
            'received'  => 'Recibida',
            'cancelled' => 'Cancelada',
        ];
    @endphp

    <table>
        <thead>
            <tr>
                <th>Referencia</th>
                <th>Proveedor</th>
                <th>Bodega</th>
                <th class="right">Ítems</th>
                <th class="right">Total est.</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Entrega esp.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                @php $total = $order->items->sum(fn($i) => $i->quantity_ordered * ($i->unit_cost ?? 0)); @endphp
                <tr>
                    <td style="font-family: monospace; font-weight: bold; color: #4f46e5;">{{ $order->reference }}</td>
                    <td>{{ $order->supplier->name }}</td>
                    <td style="color:#64748b">{{ $order->warehouse->code }} — {{ $order->warehouse->name }}</td>
                    <td class="right">{{ $order->items_count }}</td>
                    <td class="right"><strong>${{ number_format($total, 0, ',', '.') }}</strong></td>
                    <td><span class="badge badge-{{ $order->status }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                    <td style="color:#64748b">{{ $order->created_at->format('d/m/Y') }}</td>
                    <td style="color:#64748b">{{ $order->expected_at ? $order->expected_at->format('d/m/Y') : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Veltory — Sistema de Gestión de Inventario &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
