<?php

namespace App\Http\Requests;

use App\Enums\TypesUnits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:50', 'unique:units,name'],
            'abbreviation' => ['required', 'string', 'max:10'],
            'type'         => ['required', new Enum(TypesUnits::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El nombre es obligatorio.',
            'name.unique'           => 'Ya existe una unidad con ese nombre.',
            'abbreviation.required' => 'La abreviatura es obligatoria.',
            'type.required'         => 'El tipo es obligatorio.',
        ];
    }
}
