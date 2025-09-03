<?php

namespace App\Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use App\Core\Models\Customer;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'order_number', 'status', 'total_amount', 
        'tax_amount', 'shipping_amount', 'discount_amount',
        'payment_status', 'payment_method', 'shipping_address'
    ];
    
    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_address' => 'array'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Polymorphic relationship for loyalty points
    public function loyaltyPoints()
    {
        if (class_exists('App\Modules\Loyalty\Models\LoyaltyPoint')) {
            return $this->morphMany('App\Modules\Loyalty\Models\LoyaltyPoint', 'source');
        }
        return collect();
    }
}