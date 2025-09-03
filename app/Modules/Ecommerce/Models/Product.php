<?php

namespace App\Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sku', 'name', 'description', 'price', 'cost', 'stock_quantity',
        'low_stock_threshold', 'category', 'status', 'weight', 'dimensions'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer'
    ];
    
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'unit_price', 'total_price');
    }
    
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
    
    public function decreaseStock($quantity)
    {
        $this->decrement('stock_quantity', $quantity);
    }
}