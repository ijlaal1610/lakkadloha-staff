<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_number', 'product_id', 'staff_id', 'quantity',
        'selling_price', 'total_amount', 'status', 'customer_name',
        'customer_phone', 'notes', 'sold_at',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
        'sold_at' => 'datetime',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
    public function refunds() { return $this->hasMany(SaleRefund::class); }

    public static function generateSaleNumber(): string
    {
        $prefix = 'LL';
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
    public function scopeToday($query) { return $query->whereDate('sold_at', today()); }
    public function scopeThisWeek($query) { return $query->whereBetween('sold_at', [now()->startOfWeek(), now()->endOfWeek()]); }
    public function scopeThisMonth($query) { return $query->whereMonth('sold_at', now()->month)->whereYear('sold_at', now()->year); }
}
