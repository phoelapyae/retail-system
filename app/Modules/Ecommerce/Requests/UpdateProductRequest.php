<?php

namespace App\Modules\Ecommerce\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'sku' => [
                'sometimes', 
                'required', 
                'string',
                Rule::unique('products')->ignore($this->route('id'))
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'low_stock_threshold' => 'sometimes|required|integer|min:0',
            'category' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array'
        ];
    }
}