<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemTopping extends Model
{
    protected $fillable = ['order_item_id', 'topping_id', 'price'];

    public function topping(): BelongsTo
    {
        return $this->belongsTo(Topping::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
