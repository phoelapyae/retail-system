<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Core\Models\Customer;

class PosSale extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id', 'total_amount', 'tax_amount', 'discount_amount',
        'payment_method', 'status', 'register_id', 'cashier_id', 'receipt_number'
    ];
    
    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function items()
    {
        return $this->hasMany(PosItem::class);
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