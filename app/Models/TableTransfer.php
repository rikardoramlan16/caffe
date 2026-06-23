<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableTransfer extends Model
{
    protected $fillable = ['order_id', 'from_table_id', 'to_table_id', 'moved_by_user_id', 'reason'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
