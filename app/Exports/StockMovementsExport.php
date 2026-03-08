<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        $query = StockMovement::with(['product', 'user', 'warehouse'])->latest();

        if ($search = $this->filters['search'] ?? null) {
            $query->whereHas('product', fn ($q) => $q->where('name', 'ilike', "%{$search}%")->orWhere('sku', 'ilike', "%{$search}%"));
        }

        if ($type = $this->filters['type'] ?? null) {
            $query->where('type', $type);
        }

        if ($from = $this->filters['from'] ?? null) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $this->filters['to'] ?? null) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Fecha', 'Producto', 'SKU', 'Tipo', 'Cantidad', 'Antes', 'Después', 'Bodega', 'Referencia', 'Usuario'];
    }

    public function map($mov): array
    {
        return [
            $mov->created_at->format('d/m/Y H:i'),
            $mov->product?->name ?? '—',
            $mov->product?->sku ?? '—',
            $mov->type->label(),
            $mov->quantity,
            $mov->quantity_before,
            $mov->quantity_after,
            $mov->warehouse?->name ?? '—',
            $mov->reference ?? '—',
            $mov->user?->name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
