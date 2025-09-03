<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $fillable = [
        'pos_sale_id', 'product_sku', 'product_name', 'quantity', 
        'unit_price', 'total_price'
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];
    
    public function sale()
    {
        return $this->belongsTo(PosSale::class);
    }
}