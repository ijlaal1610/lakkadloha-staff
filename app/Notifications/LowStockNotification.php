<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'low_stock',
            'title' => 'Low Stock Alert',
            'message' => "'{$this->product->name}' is running low. Current stock: {$this->product->current_stock}",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->product->current_stock,
            'threshold' => $this->product->low_stock_threshold,
            'url' => route('inventory.show', $this->product),
        ];
    }
}
