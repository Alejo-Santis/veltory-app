<?php

namespace App\Models;

use App\Enums\TypeStockMovement;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasUuid;

    protected $fillable = [
        'product_id', 'user_id', 'type', 'quantity',
        'quantity_before', 'quantity_after', 'unit_cost', 'reference', 'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'type' => TypeStockMovement::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
