<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeletionApproval extends Model
{
    protected $fillable = [
        'table_name',
        'record_id',
        'data_summary',
        'requested_by',
        'reason',
        'status'
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
