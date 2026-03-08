<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; padding: 20px 24px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; }
        .header-title h1 { font-size: 17px; color: #4f46e5; font-weight: bold; }
        .header-title p { font-size: 9px; color: #64748b; margin-top: 2px; }
        .header-meta { text-align: right; font-size: 8px; color: #94a3b8; }

        /* Summary cards */
        .cards { display: flex; gap: 10px; margin-bottom: 18px; }
        .card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; }
        .card-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        .card-value.green { color: #059669; }
        .card-value.indigo { color: #4f46e5; }

        /* Section heading */
        .section-title { font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase;
                         letter-spacing: 0.08em; margin-bottom: 6px; margin-top: 14px; }
        .section-title:first-of-type { margin-top: 0; }

        /* Two-column layout */
        .two-col { display: flex; gap: 14px; }
        .col { flex: 1; min-width: 0; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: bold; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
        tbody td.mono { font-family: DejaVu Sans Mono, monospace; font-size: 7.5px; color: #64748b; }

        /* Badge-like stock status */
        .stock-ok  { color: #059669; font-weight: bold; }
        .stock-low { color: #d97706; font-weight: bold; }
        .stock-out { color: #dc2626; font-weight: bold; }

        /* Totals row */
        tfoot tr { background: #ede9fe; }
        tfoot td { padding: 5px 6px; font-weight: bold; font-size: 8.5px; }
        tfoot td.right { text-align: right; }

        /* Footer */
        .footer { margin-top: 16px; text-align: right; color: #94a3b8; font-size: 8px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-title">
            <h1>Reporte de Inventario</h1>
            <p>Período: últimos {{ $periodLabel }} &nbsp;·&nbsp; Top 20 por sección</p>
        </div>
        <div class="header-meta">
            Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Summary cards -->
    <div class="cards">
        <div class="card">
            <div class="card-label">Productos con stock</div>
            <div class="card-value indigo">{{ $totals['productos'] }}</div>
        </div>
        <div class="card">
            <div class="card-label">Valor a costo</div>
            <div class="card-value">${{ number_format($totals['valor_costo'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Valor a precio venta</div>
            <div class="card-value green">${{ number_format($totals['valor_venta'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Margen potencial</div>
            <div class="card-value {{ $totals['margen'] >= 0 ? 'green' : 'stock-out' }}">{{ $totals['margen'] }}%</div>
        </div>
    </div>

    <!-- Two columns: Rotación + Estancados -->
    <div class="two-col">

        <!-- Mayor rotación -->
        <div class="col">
            <div class="section-title">&#9679; Mayor rotación — últimos {{ $periodLabel }}</div>
            @if($rotation->isEmpty())
                <p style="color:#94a3b8; font-size:8px; padding: 8px 0;">Sin movimientos de salida en el período.</p>
            @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="right">Salidas</th>
                        <th class="right">Movim.</th>
                        <th class="right">Stock actual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rotation as $i => $item)
                    @php
                        $stockClass = $item->stock_quantity <= 0 ? 'stock-out'
                            : ($item->min_stock > 0 && $item->stock_quantity <= $item->min_stock ? 'stock-low' : 'stock-ok');
                    @endphp
                    <tr>
                        <td class="mono">{{ $i + 1 }}</td>
                        <td>
                            {{ $item->name }}
                            @if($item->sku)<span style="color:#94a3b8;"> · {{ $item->sku }}</span>@endif
                        </td>
                        <td class="right" style="color:#059669; font-weight:bold;">
                            {{ number_format($item->total_salidas, 0) }}
                            @if($item->unit_abbr)<span style="color:#94a3b8; font-weight:normal;">{{ $item->unit_abbr }}</span>@endif
                        </td>
                        <td class="right" style="color:#64748b;">{{ $item->num_movimientos }}</td>
                        <td class="right {{ $stockClass }}">{{ number_format($item->stock_quantity, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <!-- Stock estancado -->
        <div class="col">
            <div class="section-title">&#9679; Sin movimiento (inventario estancado)</div>
            @if($sinMovimiento->isEmpty())
                <p style="color:#94a3b8; font-size:8px; padding: 8px 0;">Todos los productos tuvieron movimiento en el período.</p>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="right">Stock detenido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sinMovimiento as $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                            @if($item->sku)<span style="color:#94a3b8;"> · {{ $item->sku }}</span>@endif
                        </td>
                        <td class="right" style="color:#d97706; font-weight:bold;">{{ number_format($item->stock_quantity, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div>

    <!-- Valorización -->
    <div class="section-title" style="margin-top: 16px;">&#9679; Valorización de inventario — Top 20 por valor</div>
    @if($valorizacion->isEmpty())
        <p style="color:#94a3b8; font-size:8px; padding: 8px 0;">Sin productos con stock.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th class="right">Stock</th>
                <th class="right">Costo unit.</th>
                <th class="right">P. Venta unit.</th>
                <th class="right">Valor costo</th>
                <th class="right">Valor venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($valorizacion as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td class="mono">{{ $item->sku ?? '—' }}</td>
                <td class="right">
                    {{ number_format($item->stock_quantity, 0) }}
                    @if($item->unit_abbr)<span style="color:#94a3b8;"> {{ $item->unit_abbr }}</span>@endif
                </td>
                <td class="right">${{ number_format($item->cost_price ?? 0, 0, ',', '.') }}</td>
                <td class="right">${{ number_format($item->sale_price ?? 0, 0, ',', '.') }}</td>
                <td class="right" style="font-weight:bold;">${{ number_format($item->valor_costo, 0, ',', '.') }}</td>
                <td class="right" style="font-weight:bold; color:#059669;">${{ number_format($item->valor_venta, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="font-weight:bold;">TOTALES</td>
                <td class="right">${{ number_format($totals['valor_costo'], 0, ',', '.') }}</td>
                <td class="right" style="color:#059669;">${{ number_format($totals['valor_venta'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">Veltory — Sistema de Inventario</div>
</body>
</html>
