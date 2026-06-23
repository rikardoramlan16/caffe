<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSession extends Model
{
    protected $fillable = ['branch_id', 'table_id', 'customer_token', 'last_seen_at'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime'];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class, 'table_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
