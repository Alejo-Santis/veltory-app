<?php

namespace App\Http\Requests;

use App\Enums\TypeWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $warehouse = $this->route('warehouse');

        return [
            'code'         => ['required', 'string', 'max:20', "unique:warehouses,code,{$warehouse->id}"],
            'name'         => ['required', 'string', 'max:150'],
            'type'         => ['required', new Enum(TypeWarehouse::class)],
            'address'      => ['nullable', 'string'],
            'city'         => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'manager_name' => ['nullable', 'string', 'max:150'],
            'is_active'    => ['boolean'],
            'notes'        => ['nullable', 'string'],
        ];
    }
}
