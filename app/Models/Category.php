<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['branch_id', 'name', 'sort_order', 'is_active'];

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
