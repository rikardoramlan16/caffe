<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['branch_id', 'category_id', 'name', 'description', 'price', 'image_path', 'is_available', 'is_featured'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function toppings(): BelongsToMany
    {
        return $this->belongsToMany(Topping::class, 'menu_topping')->withTimestamps();
    }
}
