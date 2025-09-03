<?php

namespace App\Modules\Loyalty\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Loyalty\Services\TierService;
use App\Modules\Loyalty\Models\LoyaltyTier;

class TierController extends Controller
{
    private $tierService;
    
    public function __construct(TierService $tierService)
    {
        $this->tierService = $tierService;
    }
    
    public function index()
    {
        $tiers = LoyaltyTier::orderBy('min_points')->get();
        
        return response()->json([
            'success' => true,
            'data' => $tiers
        ]);
    }
    
    public function customerTierInfo($customerId)
    {
        $tierInfo = $this->tierService->getCustomerTierInfo($customerId);
        
        return response()->json([
            'success' => true,
            'data' => $tierInfo
        ]);
    }
}