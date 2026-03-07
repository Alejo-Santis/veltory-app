<?php

namespace App\Models;

use App\Enums\TypeWarehouse;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'type', 'address', 'city',
        'phone', 'manager_name', 'is_active', 'notes',
    ];

    protected $casts = [
        'type'      => TypeWarehouse::class,
        'is_active' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function stock(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'warehouse_stock')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_warehouse_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_warehouse_id');
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
