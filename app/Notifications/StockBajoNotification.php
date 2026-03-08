<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;

class StockBajoNotification extends Notification
{
    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'stock_bajo',
            'title'   => 'Stock bajo: ' . $this->product->name,
            'body'    => "Quedan {$this->product->stock_quantity} unidades (mínimo: {$this->product->min_stock})",
            'href'    => '/products/' . $this->product->uuid,
            'icon'    => 'warning',
            'product' => [
                'uuid' => $this->product->uuid,
                'name' => $this->product->name,
                'sku'  => $this->product->sku,
            ],
        ];
    }
}
