<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 28px 32px;
            background: #fff;
        }

        /* ── Header ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 16px;
        }
        .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }

        .brand-name { font-size: 20px; font-weight: bold; color: #4f46e5; }
        .brand-sub  { font-size: 10px; color: #64748b; }

        .doc-title  { font-size: 22px; font-weight: bold; color: #1e293b; }
        .doc-ref    { font-size: 14px; font-weight: bold; color: #4f46e5; font-family: 'Courier New', monospace; }
        .doc-date   { font-size: 10px; color: #64748b; margin-top: 2px; }

        /* ── Status badge ── */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; margin-left: 6px; }
        .badge-draft     { background: #f1f5f9; color: #475569; }
        .badge-sent      { background: #ede9fe; color: #5b21b6; }
        .badge-partial   { background: #fef3c7; color: #92400e; }
        .badge-received  { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        /* ── Info boxes ── */
        .info-row { display: table; width: 100%; margin-bottom: 20px; border-spacing: 10px; }
        .info-box {
            display: table-cell;
            width: 33.33%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            vertical-align: top;
        }
        .info-box h3 { font-size: 9px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.08em; margin: 0 0 6px; }
        .info-box p  { font-size: 11px; color: #1e293b; margin: 2px 0; }
        .info-box .highlight { font-weight: bold; font-size: 12px; color: #4f46e5; }

        /* ── Tabla ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 8px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #f1f5f9; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        tbody td.right { text-align: right; }
        tbody td.mono  { font-family: 'Courier New', monospace; font-size: 10px; color: #64748b; }

        /* ── Totales ── */
        .totals { margin-left: auto; width: 240px; border-collapse: collapse; }
        .totals td { padding: 5px 10px; font-size: 11px; }
        .totals .label { color: #64748b; text-align: right; }
        .totals .value { text-align: right; font-weight: bold; }
        .totals .grand { background: #4f46e5; color: #fff; border-radius: 4px; }
        .totals .grand .label { color: #c7d2fe; font-size: 10px; }
        .totals .grand .value { font-size: 14px; }

        /* ── Notas ── */
        .notes-box {
            margin-top: 12px;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 10px 14px;
            border-radius: 0 4px 4px 0;
        }
        .notes-box h4 { font-size: 9px; text-transform: uppercase; color: #92400e; margin: 0 0 4px; }
        .notes-box p  { font-size: 10px; color: #78350f; margin: 0; }

        /* ── Progress bar items ── */
        .progress-cell { width: 80px; }
        .progress-bar  { background: #e2e8f0; border-radius: 3px; height: 5px; margin-top: 3px; }
        .progress-fill { background: #10b981; border-radius: 3px; height: 5px; }

        /* ── Footer ── */
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; color: #94a3b8; font-size: 9px; }
        .footer-right { display: table-cell; text-align: right; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="brand-name">Veltory</div>
            <div class="brand-sub">Sistema de Gestión de Inventario</div>
        </div>
        <div class="header-right">
            <div class="doc-title">ORDEN DE COMPRA</div>
            <div class="doc-ref">
                {{ $order->reference }}
                @php
                    $statusLabels = [
                        'draft'     => 'BORRADOR',
                        'sent'      => 'ENVIADA',
                        'partial'   => 'PARCIAL',
                        'received'  => 'RECIBIDA',
                        'cancelled' => 'CANCELADA',
                    ];
                @endphp
                <span class="badge badge-{{ $order->status }}">{{ $statusLabels[$order->status] ?? strtoupper($order->status) }}</span>
            </div>
            <div class="doc-date">Emitida: {{ $order->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    <!-- Info boxes -->
    <div class="info-row">
        <div class="info-box">
            <h3>Proveedor</h3>
            <p class="highlight">{{ $order->supplier->name }}</p>
            @if($order->supplier->contact_name)
                <p>Contacto: {{ $order->supplier->contact_name }}</p>
            @endif
            @if($order->supplier->email)
                <p>{{ $order->supplier->email }}</p>
            @endif
            @if($order->supplier->phone)
                <p>Tel: {{ $order->supplier->phone }}</p>
            @endif
        </div>
        <div class="info-box">
            <h3>Bodega de recepción</h3>
            <p class="highlight">{{ $order->warehouse->name }}</p>
            <p style="font-family: monospace; font-size:10px; color:#64748b;">{{ $order->warehouse->code }}</p>
            @if($order->warehouse->address)
                <p style="color:#64748b; font-size:10px;">{{ $order->warehouse->address }}</p>
            @endif
        </div>
        <div class="info-box">
            <h3>Fechas</h3>
            <p>Creada: <strong>{{ $order->created_at->format('d/m/Y') }}</strong></p>
            @if($order->expected_at)
                <p>Entrega esperada: <strong>{{ $order->expected_at->format('d/m/Y') }}</strong></p>
            @endif
            @if($order->sent_at)
                <p>Enviada: <strong>{{ $order->sent_at->format('d/m/Y') }}</strong></p>
            @endif
            @if($order->received_at)
                <p>Recibida: <strong>{{ $order->received_at->format('d/m/Y') }}</strong></p>
            @endif
            @if($order->createdBy)
                <p style="margin-top:6px; color:#64748b;">Creada por: {{ $order->createdBy->name }}</p>
            @endif
        </div>
    </div>

    <!-- Tabla de ítems -->
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Producto</th>
                <th>SKU</th>
                <th class="right">Pedido</th>
                <th class="right">Recibido</th>
                <th class="right">Pendiente</th>
                <th class="right">Costo unit.</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
                @php
                    $pending  = $item->quantity_ordered - $item->quantity_received;
                    $subtotal = $item->quantity_ordered * ($item->unit_cost ?? 0);
                    $pct      = $item->quantity_ordered > 0 ? ($item->quantity_received / $item->quantity_ordered * 100) : 0;
                @endphp
                <tr>
                    <td style="color:#94a3b8">{{ $i + 1 }}</td>
                    <td><strong>{{ $item->product->name }}</strong></td>
                    <td class="mono">{{ $item->product->sku ?? '—' }}</td>
                    <td class="right">{{ $item->quantity_ordered }} {{ $item->product->unit?->abbreviation }}</td>
                    <td class="right" style="color: {{ $item->quantity_received > 0 ? '#059669' : '#94a3b8' }}">
                        {{ $item->quantity_received }}
                        @if($item->quantity_received > 0)
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        @endif
                    </td>
                    <td class="right" style="color: {{ $pending > 0 ? '#d97706' : '#64748b' }}">
                        {{ $pending }}
                    </td>
                    <td class="right" style="color:#64748b">
                        {{ $item->unit_cost ? '$' . number_format($item->unit_cost, 0, ',', '.') : '—' }}
                    </td>
                    <td class="right"><strong>${{ number_format($subtotal, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    @php
        $total    = $order->items->sum(fn($i) => $i->quantity_ordered * ($i->unit_cost ?? 0));
        $recibido = $order->items->sum(fn($i) => $i->quantity_received * ($i->unit_cost ?? 0));
    @endphp
    <table class="totals">
        <tr>
            <td class="label">Subtotal pedido</td>
            <td class="value">${{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        @if($order->status !== 'draft')
        <tr>
            <td class="label">Valor recibido</td>
            <td class="value" style="color:#059669">${{ number_format($recibido, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td class="label">TOTAL ORDEN</td>
            <td class="value">${{ number_format($total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Notas -->
    @if($order->notes)
        <div class="notes-box">
            <h4>Observaciones</h4>
            <p>{{ $order->notes }}</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            Veltory &mdash; Sistema de Gestión de Inventario &nbsp;|&nbsp; Documento generado el {{ now()->format('d/m/Y H:i') }}
        </div>
        <div class="footer-right">
            {{ $order->reference }} &nbsp;&mdash;&nbsp; Pág. 1
        </div>
    </div>

</body>
</html>
