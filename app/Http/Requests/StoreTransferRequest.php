<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'from_warehouse_id'      => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id'        => ['required', 'exists:warehouses,id'],
            'notes'                  => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.notes'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_warehouse_id.required'  => 'Selecciona la bodega origen.',
            'from_warehouse_id.different' => 'La bodega origen y destino no pueden ser la misma.',
            'to_warehouse_id.required'    => 'Selecciona la bodega destino.',
            'items.required'              => 'Agrega al menos un producto al traslado.',
            'items.min'                   => 'Agrega al menos un producto al traslado.',
            'items.*.product_id.required' => 'Selecciona un producto.',
            'items.*.quantity.min'        => 'La cantidad mínima es 1.',
        ];
    }
}
