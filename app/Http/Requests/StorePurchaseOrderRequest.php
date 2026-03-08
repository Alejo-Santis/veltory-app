<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id'              => 'required|exists:suppliers,id',
            'warehouse_id'             => 'required|exists:warehouses,id',
            'expected_at'              => 'nullable|date',
            'notes'                    => 'nullable|string|max:2000',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost'        => 'nullable|numeric|min:0',
            'items.*.notes'            => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                    => 'Debe agregar al menos un producto.',
            'items.min'                         => 'Debe agregar al menos un producto.',
            'items.*.product_id.required'       => 'Selecciona un producto.',
            'items.*.quantity_ordered.required' => 'La cantidad es obligatoria.',
            'items.*.quantity_ordered.min'      => 'La cantidad mínima es 1.',
        ];
    }
}
