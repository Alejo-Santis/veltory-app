<?php

namespace App\Enums;

enum TypeStockMovement: string
{
    case IN         = 'in';
    case OUT        = 'out';
    case ADJUSTMENT = 'adjustment';
    case RETURN     = 'return';
    case LOSS       = 'loss';

    public function label(): string
    {
        return match ($this) {
            self::IN         => 'Entrada',
            self::OUT        => 'Salida',
            self::ADJUSTMENT => 'Ajuste',
            self::RETURN     => 'Devolución',
            self::LOSS       => 'Pérdida',
        };
    }
}
