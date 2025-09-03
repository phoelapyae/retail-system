<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Core\Traits\SafeRelationships;

class Customer extends Model
{
    use HasFactory, SafeRelationships;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'date_of_birth'
    ];
    
    protected $casts = [
        'date_of_birth' => 'date'
    ];

    // Safe relationships to optional modules
    public function loyaltyPoints()
    {
        return $this->safeHasMany('App\Modules\Loyalty\Models\LoyaltyPoint', 'customer_id');
    }
    
    public function ecommerceOrders()
    {
        return $this->safeHasMany('App\Modules\Ecommerce\Models\Order', 'customer_id');
    }
    
    public function posSales()
    {
        return $this->safeHasMany('App\Modules\POS\Models\PosSale', 'customer_id');
    }
}