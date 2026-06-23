<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $fillable = [
        'opname_number',
        'status' // DRAFT, ADJUSTED
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
