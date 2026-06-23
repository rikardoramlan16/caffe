<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'inventory_category_id',
        'name',
        'unit',
        'current_stock',
        'min_stock'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(MenuRecipe::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'Habis';
        }
        if ($this->current_stock <= $this->min_stock) {
            return 'Menipis';
        }
        return 'Aman';
    }
}
