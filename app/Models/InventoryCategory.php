<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    protected $fillable = ['name'];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'inventory_category_id');
    }
}
