<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_order_id', 'product_id',
        'quantity_ordered', 'quantity_received', 'unit_cost', 'notes',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function order()   { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id'); }

    public function getPendingQuantityAttribute(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity_ordered * ($this->unit_cost ?? 0);
    }
}
