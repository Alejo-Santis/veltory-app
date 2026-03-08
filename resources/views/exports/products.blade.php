<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
        h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
        .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #f1f5f9; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
        .ok    { background: #d1fae5; color: #065f46; }
        .low   { background: #fef3c7; color: #92400e; }
        .out   { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 24px; text-align: right; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Reporte de Productos</h1>
    <div class="meta">Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; Total: {{ count($products) }} productos</div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th>Proveedor</th>
                <th>P. Costo</th>
                <th>P. Venta</th>
                <th>Stock</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->categories->pluck('name')->join(', ') ?: '—' }}</td>
                <td>{{ $product->unit?->name ?? '—' }}</td>
                <td>{{ $product->supplier?->name ?? '—' }}</td>
                <td>$ {{ number_format($product->cost_price, 2) }}</td>
                <td>$ {{ number_format($product->sale_price, 2) }}</td>
                <td>{{ $product->stock_quantity }}</td>
                <td>
                    @php
                        $status = $product->stock_quantity <= 0 ? 'out' : ($product->stock_quantity <= $product->min_stock && $product->min_stock > 0 ? 'low' : 'ok');
                        $labels = ['ok' => 'Normal', 'low' => 'Bajo', 'out' => 'Sin stock'];
                    @endphp
                    <span class="badge {{ $status }}">{{ $labels[$status] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Veltory — Sistema de Inventario</div>
</body>
</html>
