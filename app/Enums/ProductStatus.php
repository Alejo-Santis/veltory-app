<?php

namespace App\Enums;

enum ProductStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
    case DRAFT    = 'draft';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Activo',
            self::INACTIVE => 'Inactivo',
            self::DRAFT    => 'Borrador',
            self::ARCHIVED => 'Archivado',
        };
    }
}
