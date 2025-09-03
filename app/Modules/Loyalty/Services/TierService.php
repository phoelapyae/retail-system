<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\{LoyaltyPoint, LoyaltyTier};
use App\Core\Models\Customer;
use App\Core\Services\ModuleCommunicationHub;

class TierService
{
    private $hub;
    
    public function __construct(ModuleCommunicationHub $hub)
    {
        $this->hub = $hub;
    }
    
    public function updateCustomerTier($customerId)
    {
        $totalPoints = LoyaltyPoint::where('customer_id', $customerId)
            ->where('transaction_type', 'earned')
            ->sum('points');
            
        $tier = LoyaltyTier::getTierByPoints($totalPoints);
        
        if ($tier) {
            // Update customer tier if different
            $customer = Customer::find($customerId);
            if ($customer && $customer->loyalty_tier_id !== $tier->id) {
                $customer->update(['loyalty_tier_id' => $tier->id]);
                
                // Notify about tier upgrade
                $this->hub->notify(TierUpgraded::class, [
                    'customer_id' => $customerId,
                    'old_tier' => $customer->loyalty_tier_id,
                    'new_tier' => $tier->id,
                    'total_points' => $totalPoints
                ]);
            }
        }
        
        return $tier;
    }
    
    public function getCustomerTierInfo($customerId)
    {
        $customer = Customer::with('loyaltyTier')->find($customerId);
        $totalPoints = LoyaltyPoint::where('customer_id', $customerId)->sum('points');
        $earnedPoints = LoyaltyPoint::where('customer_id', $customerId)
            ->where('transaction_type', 'earned')
            ->sum('points');
            
        $nextTier = LoyaltyTier::where('min_points', '>', $earnedPoints)
            ->orderBy('min_points', 'asc')
            ->first();
            
        return [
            'current_tier' => $customer?->loyaltyTier,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'next_tier' => $nextTier,
            'points_to_next_tier' => $nextTier ? $nextTier->min_points - $earnedPoints : 0
        ];
    }
}