<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'               => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'barcode'           => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'unit_id'           => ['nullable', 'exists:units,id'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'cost_price'        => ['nullable', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0'],
            'compare_price'     => ['nullable', 'numeric', 'min:0'],
            'tax_rate'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock_quantity'    => ['nullable', 'integer', 'min:0'],
            'min_stock'         => ['nullable', 'integer', 'min:0'],
            'max_stock'         => ['nullable', 'integer', 'min:0'],
            'track_stock'       => ['boolean'],
            'allow_backorder'   => ['boolean'],
            'weight'            => ['nullable', 'numeric', 'min:0'],
            'dimensions_length' => ['nullable', 'numeric', 'min:0'],
            'dimensions_width'  => ['nullable', 'numeric', 'min:0'],
            'dimensions_height' => ['nullable', 'numeric', 'min:0'],
            'status'            => ['required', new Enum(ProductStatus::class)],
            'featured'          => ['boolean'],
            'notes'             => ['nullable', 'string'],
            'categories'        => ['nullable', 'array'],
            'categories.*'      => ['exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'El nombre del producto es obligatorio.',
            'sku.unique'          => 'Este SKU ya está registrado.',
            'barcode.unique'      => 'Este código de barras ya está registrado.',
            'unit_id.exists'      => 'La unidad seleccionada no existe.',
            'supplier_id.exists'  => 'El proveedor seleccionado no existe.',
            'status.Enum'         => 'El estado seleccionado no es válido.',
            'categories.*.exists' => 'Una de las categorías seleccionadas no existe.',
        ];
    }
}
