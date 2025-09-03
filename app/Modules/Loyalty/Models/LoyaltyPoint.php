<?php

namespace App\Modules\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use App\Core\Models\Customer;

class LoyaltyPoint extends Model
{
    protected $fillable = [
        'customer_id', 'points', 'source_type', 'source_id', 
        'transaction_type', 'description', 'expires_at'
    ];
    
    protected $casts = [
        'points' => 'integer',
        'expires_at' => 'datetime'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    // Polymorphic relationship
    public function source()
    {
        return $this->morphTo();
    }
}