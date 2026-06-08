<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'photo', 'current_stock', 'low_stock_threshold',
        'notes', 'created_by', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'current_stock' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function sales() { return $this->hasMany(Sale::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('public/storage/' . $this->photo);
        }
        return asset('images/product-placeholder.png');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) return 'out_of_stock';
        if ($this->isLowStock()) return 'low_stock';
        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            default => 'In Stock',
        };
    }

    public function addStock(int $quantity, int $userId, string $notes = null): StockMovement
    {
        $before = $this->current_stock;
        $this->increment('current_stock', $quantity);
        return $this->stockMovements()->create([
            'user_id' => $userId,
            'type' => 'add',
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $this->current_stock,
            'notes' => $notes,
        ]);
    }

    public function deductStock(int $quantity, int $userId, string $type = 'deduct', string $notes = null): StockMovement
    {
        $before = $this->current_stock;
        $this->decrement('current_stock', $quantity);
        return $this->stockMovements()->create([
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $this->current_stock,
            'notes' => $notes,
        ]);
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeLowStock($query) { return $query->whereColumn('current_stock', '<=', 'low_stock_threshold'); }
}
