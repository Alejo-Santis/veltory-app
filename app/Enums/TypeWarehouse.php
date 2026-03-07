<?php

namespace App\Enums;

enum TypeWarehouse: string
{
    case WAREHOUSE = 'warehouse';
    case BRANCH    = 'branch';
    case STORE     = 'store';

    public function label(): string
    {
        return match($this) {
            self::WAREHOUSE => 'Bodega',
            self::BRANCH    => 'Sucursal',
            self::STORE     => 'Tienda',
        };
    }
}
