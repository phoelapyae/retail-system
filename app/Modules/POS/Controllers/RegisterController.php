<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Register;
use App\Modules\POS\Requests\OpenRegisterRequest;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function index()
    {
        $registers = Register::with(['sales' => function($query) {
            $query->whereDate('created_at', today());
        }])->get();
        
        return response()->json([
            'success' => true,
            'data' => $registers
        ]);
    }
    
    public function open(OpenRegisterRequest $request, $id)
    {
        $register = Register::findOrFail($id);
        $register->openRegister($request->opening_balance);
        
        return response()->json([
            'success' => true,
            'data' => $register,
            'message' => 'Register opened successfully'
        ]);
    }
    
    public function close($id)
    {
        $register = Register::findOrFail($id);
        $register->closeRegister();
        
        return response()->json([
            'success' => true,
            'data' => $register,
            'message' => 'Register closed successfully'
        ]);
    }
    
    public function dailyReport($id)
    {
        $register = Register::findOrFail($id);
        $sales = $register->sales()->whereDate('created_at', today())->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'register' => $register,
                'sales_count' => $sales->count(),
                'total_revenue' => $sales->sum('total_amount'),
                'sales' => $sales
            ]
        ]);
    }
}