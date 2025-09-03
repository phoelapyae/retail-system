<?php

namespace App\Modules\Ecommerce\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'cost' => $this->cost,
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'category' => $this->category,
            'status' => $this->status,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'is_low_stock' => $this->isLowStock(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
