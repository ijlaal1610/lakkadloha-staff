<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleRefund extends Model
{
    protected $fillable = [
        'sale_id', 'processed_by', 'quantity', 'refund_amount', 'reason', 'refunded_at',
    ];

    protected $casts = [
        'refunded_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    public function sale() { return $this->belongsTo(Sale::class); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }
}
