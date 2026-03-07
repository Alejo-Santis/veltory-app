<?php

namespace App\Enums;

enum TypesUnits: string
{
    case WEIGHT = 'weight';
    case VOLUME = 'volume';
    case LENGTH = 'length';
    case UNIT   = 'unit';
    case OTHER  = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WEIGHT => 'Peso',
            self::VOLUME => 'Volumen',
            self::LENGTH => 'Longitud',
            self::UNIT   => 'Unidad',
            self::OTHER  => 'Otro',
        };
    }
}
