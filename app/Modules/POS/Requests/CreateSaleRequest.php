<?php

namespace App\Modules\POS\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSaleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,card,mobile',
            'customer' => 'nullable|array',
            'customer.name' => 'nullable|string',
            'customer.email' => 'nullable|email',
            'customer.phone' => 'nullable|string'
        ];
    }
}