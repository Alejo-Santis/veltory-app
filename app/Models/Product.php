<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'sku', 'barcode', 'name', 'slug', 'description', 'short_description',
        'unit_id', 'supplier_id', 'cost_price', 'sale_price', 'compare_price', 'tax_rate',
        'stock_quantity', 'min_stock', 'max_stock', 'track_stock', 'allow_backorder',
        'weight', 'dimensions_length', 'dimensions_width', 'dimensions_height',
        'status', 'featured', 'notes', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'cost_price'   => 'decimal:2',
        'sale_price'   => 'decimal:2',
        'compare_price' => 'decimal:2',
        'tax_rate'     => 'decimal:2',
        'track_stock'  => 'boolean',
        'allow_backorder' => 'boolean',
        'featured'     => 'boolean',
        'status'       => ProductStatus::class,
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->withPivot('is_primary');
    }

    public function primaryCategory(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->withPivot('is_primary')
            ->wherePivot('is_primary', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_cover', true);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->min_stock && $this->min_stock > 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) return 'out';
        if ($this->stock_quantity <= $this->min_stock) return 'low';
        return 'ok';
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::ACTIVE);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('min_stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }
}
