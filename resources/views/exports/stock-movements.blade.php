<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 20px; }
        h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
        .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 6px 7px; text-align: left; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f1f5f9; }
        tbody td { padding: 5px 7px; border-bottom: 1px solid #e2e8f0; }
        .in     { color: #059669; font-weight: bold; }
        .out    { color: #dc2626; font-weight: bold; }
        .adj    { color: #d97706; font-weight: bold; }
        .footer { margin-top: 24px; text-align: right; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Reporte de Movimientos de Stock</h1>
    <div class="meta">Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; Total: {{ count($movements) }} movimientos</div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>SKU</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Antes</th>
                <th>Después</th>
                <th>Bodega</th>
                <th>Referencia</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $mov)
            @php
                $typeClass = in_array($mov->type->value, ['in','return']) ? 'in' : (in_array($mov->type->value, ['out','loss']) ? 'out' : 'adj');
            @endphp
            <tr>
                <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $mov->product?->name ?? '—' }}</td>
                <td>{{ $mov->product?->sku ?? '—' }}</td>
                <td class="{{ $typeClass }}">{{ $mov->type->label() }}</td>
                <td class="{{ $typeClass }}">{{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}</td>
                <td>{{ $mov->quantity_before }}</td>
                <td>{{ $mov->quantity_after }}</td>
                <td>{{ $mov->warehouse?->name ?? '—' }}</td>
                <td>{{ $mov->reference ?? '—' }}</td>
                <td>{{ $mov->user?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Veltory — Sistema de Inventario</div>
</body>
</html>
