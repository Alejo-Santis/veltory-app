<?php

namespace App\Http\Requests;

use App\Enums\TypeStockMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'         => ['required', new Enum(TypeStockMovement::class)],
            'quantity'     => ['required', 'integer', 'min:1'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'unit_cost'    => ['nullable', 'numeric', 'min:0'],
            'reference'    => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'     => 'El tipo de movimiento es obligatorio.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min'      => 'La cantidad debe ser al menos 1.',
        ];
    }
}
