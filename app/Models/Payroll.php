<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'month',
        'basic_salary',
        'allowance',
        'bonus',
        'deduction',
        'total_salary',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function detail()
    {
        return $this->hasOne(PayrollDetail::class, 'payroll_id');
    }
}
