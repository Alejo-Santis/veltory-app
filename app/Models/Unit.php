<?php

namespace App\Models;

use App\Enums\TypesUnits;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasUuid;

    protected $fillable = ['name', 'abbreviation', 'type'];

    protected $casts = [
        'type' => TypesUnits::class,
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
