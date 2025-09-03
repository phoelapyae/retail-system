<?php

namespace App\Modules\Loyalty\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Loyalty\Services\LoyaltyService;
use App\Modules\Loyalty\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    private $loyaltyService;
    
    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }
    
    public function getCustomerPoints($customerId)
    {
        $points = $this->loyaltyService->getCustomerPoints($customerId);
        $tier = $this->loyaltyService->getCustomerTier($customerId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'customer_id' => $customerId,
                'total_points' => $points,
                'tier' => $tier,
                'points_breakdown' => $this->getPointsBreakdown($customerId)
            ]
        ]);
    }
    
    public function redeemPoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);
        
        try {
            $transaction = $this->loyaltyService->redeemPoints(
                $customerId, 
                $request->points, 
                $request->description
            );
            
            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Points redeemed successfully',
                'remaining_points' => $this->loyaltyService->getCustomerPoints($customerId)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    public function getPointsHistory($customerId, Request $request)
    {
        $query = LoyaltyPoint::where('customer_id', $customerId)
                            ->orderBy('created_at', 'desc');
        
        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $history = $query->paginate($request->per_page ?? 20);
        
        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
    
    private function getPointsBreakdown($customerId)
    {
        return [
            'earned' => LoyaltyPoint::where('customer_id', $customerId)
                                  ->where('transaction_type', 'earned')
                                  ->sum('points'),
            'redeemed' => abs(LoyaltyPoint::where('customer_id', $customerId)
                                        ->where('transaction_type', 'redeemed')
                                        ->sum('points')),
            'expired' => abs(LoyaltyPoint::where('customer_id', $customerId)
                                       ->where('transaction_type', 'expired')
                                       ->sum('points'))
        ];
    }
}