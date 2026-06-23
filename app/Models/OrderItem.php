<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_id',
        'product_id',
        'quantity',
        'unit_price',
        'size',
        'size_price',
        'sugar_level',
        'ice_level',
        'note'
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function toppings(): HasMany
    {
        return $this->hasMany(OrderItemTopping::class);
    }
}
