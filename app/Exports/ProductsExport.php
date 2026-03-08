<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        $query = Product::with(['unit', 'supplier', 'categories'])->latest();

        if ($search = $this->filters['search'] ?? null) {
            $query->where(fn ($q) => $q->where('name', 'ilike', "%{$search}%")->orWhere('sku', 'ilike', "%{$search}%"));
        }

        if ($status = $this->filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['SKU', 'Nombre', 'Categoría', 'Unidad', 'Proveedor', 'Precio costo', 'Precio venta', 'Stock', 'Stock mín.', 'Estado'];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->categories->pluck('name')->join(', '),
            $product->unit?->name ?? '—',
            $product->supplier?->name ?? '—',
            $product->cost_price,
            $product->sale_price,
            $product->stock_quantity,
            $product->min_stock,
            $product->status->label(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
