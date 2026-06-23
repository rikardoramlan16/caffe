<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScannerScan extends Model
{
    protected $fillable = [
        'pairing_code',
        'product_id',
        'product_name',
        'product_price',
        'is_processed',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'is_processed' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
