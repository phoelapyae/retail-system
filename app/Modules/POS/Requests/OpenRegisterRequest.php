<?php

namespace App\Modules\POS\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'opening_balance' => 'required|numeric|min:0'
        ];
    }
}