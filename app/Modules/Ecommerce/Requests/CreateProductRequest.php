<?php

namespace App\Modules\Ecommerce\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'category' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array'
        ];
    }
}