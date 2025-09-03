<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Register extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'location', 'status', 'opening_balance', 'current_balance'
    ];
    
    protected $casts = [
        'benefits' => 'array',
        'multiplier' => 'decimal:2'
    ];
    
    public function customers()
    {
        return $this->hasMany(Customer::class, 'loyalty_tier_id');
    }
    
    public static function getTierByPoints($points)
    {
        return self::where('min_points', '<=', $points)
                   ->where('max_points', '>=', $points)
                   ->orWhere('max_points', null)
                   ->orderBy('min_points', 'desc')
                   ->first();
    }
}