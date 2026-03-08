<?php

namespace App\Notifications;

use App\Models\Transfer;
use Illuminate\Notifications\Notification;

class TransferActualizadoNotification extends Notification
{
    public function __construct(
        public Transfer $transfer,
        public string   $action,   // 'solicitado' | 'aprobado' | 'despachado' | 'completado' | 'cancelado'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $icons = [
            'solicitado' => 'info',
            'aprobado'   => 'success',
            'despachado' => 'info',
            'completado' => 'success',
            'cancelado'  => 'error',
        ];

        $ref = $this->transfer->reference ?? "Traslado #{$this->transfer->id}";

        return [
            'type'   => 'transfer_' . $this->action,
            'title'  => "Traslado {$this->action}",
            'body'   => "{$ref} — {$this->transfer->from_warehouse?->name} → {$this->transfer->to_warehouse?->name}",
            'href'   => '/transfers/' . $this->transfer->uuid,
            'icon'   => $icons[$this->action] ?? 'info',
        ];
    }
}
