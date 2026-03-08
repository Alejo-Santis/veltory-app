<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrdenCompraNotification extends Notification
{
    use Queueable;

    // accion: 'creada' | 'enviada' | 'recibida_parcial' | 'recibida' | 'cancelada'
    public function __construct(
        public readonly PurchaseOrder $order,
        public readonly string $accion
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $iconMap = [
            'creada'           => 'info',
            'enviada'          => 'info',
            'recibida_parcial' => 'warning',
            'recibida'         => 'success',
            'cancelada'        => 'error',
        ];

        $titleMap = [
            'creada'           => 'Nueva orden de compra',
            'enviada'          => 'Orden enviada al proveedor',
            'recibida_parcial' => 'Recepción parcial registrada',
            'recibida'         => 'Orden recibida completa',
            'cancelada'        => 'Orden de compra cancelada',
        ];

        $bodyMap = [
            'creada'           => "Se creó la orden {$this->order->reference} a {$this->order->supplier->name}.",
            'enviada'          => "La orden {$this->order->reference} fue enviada a {$this->order->supplier->name}.",
            'recibida_parcial' => "La orden {$this->order->reference} tiene una recepción parcial registrada.",
            'recibida'         => "La orden {$this->order->reference} de {$this->order->supplier->name} fue recibida completamente.",
            'cancelada'        => "La orden {$this->order->reference} a {$this->order->supplier->name} fue cancelada.",
        ];

        return [
            'type'  => 'orden_compra',
            'title' => $titleMap[$this->accion] ?? 'Orden de compra actualizada',
            'body'  => $bodyMap[$this->accion] ?? "Orden {$this->order->reference} — {$this->accion}.",
            'href'  => '/purchase-orders/' . $this->order->uuid,
            'icon'  => $iconMap[$this->accion] ?? 'info',
        ];
    }
}
