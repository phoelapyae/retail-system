<?php

namespace App\Modules\POS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\POSService;
use App\Modules\POS\Requests\CreateSaleRequest;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    private $posService;
    
    public function __construct(POSService $posService)
    {
        $this->posService = $posService;
    }
    
    public function create(CreateSaleRequest $request)
    {
        try {
            $sale = $this->posService->completeSale($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $sale,
                'message' => 'Sale completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete sale: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $sale = PosSale::with('items', 'customer')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $sale
        ]);
    }
}