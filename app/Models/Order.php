<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const ACTIVE_STATUSES = ['WAITING_PAYMENT', 'PAID', 'MAKING', 'READY'];

    protected $fillable = ['branch_id', 'table_id', 'customer_session_id', 'invoice_number', 'status', 'subtotal', 'service_fee', 'total', 'customer_note', 'paid_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class, 'table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(TableTransfer::class);
    }
}
