<?php

namespace App\Enums;

enum TransferStatus: string
{
    case DRAFT      = 'draft';
    case REQUESTED  = 'requested';
    case APPROVED   = 'approved';
    case IN_TRANSIT = 'in_transit';
    case COMPLETED  = 'completed';
    case CANCELLED  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT      => 'Borrador',
            self::REQUESTED  => 'Solicitado',
            self::APPROVED   => 'Aprobado',
            self::IN_TRANSIT => 'En tránsito',
            self::COMPLETED  => 'Completado',
            self::CANCELLED  => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT      => 'slate',
            self::REQUESTED  => 'amber',
            self::APPROVED   => 'indigo',
            self::IN_TRANSIT => 'blue',
            self::COMPLETED  => 'emerald',
            self::CANCELLED  => 'red',
        };
    }
}
