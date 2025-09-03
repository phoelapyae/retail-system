<?php

namespace App\Modules\Loyalty\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Loyalty\Models\LoyaltyPoint;
use App\Core\Services\ModuleCommunicationHub;
use App\Core\Events\PointsExpiring;

class ExpirePointsCommand extends Command
{
    protected $signature = 'loyalty:expire-points';
    protected $description = 'Expire loyalty points that have passed their expiration date';
    
    private $hub;
    
    public function __construct(ModuleCommunicationHub $hub)
    {
        parent::__construct();
        $this->hub = $hub;
    }

    public function handle()
    {
        $expiringPoints = LoyaltyPoint::where('expires_at', '<=', now())
                                    ->where('transaction_type', 'earned')
                                    ->get();
        
        $totalExpired = 0;
        $customersAffected = [];
        
        foreach ($expiringPoints as $point) {
            // Create expiration transaction
            LoyaltyPoint::create([
                'customer_id' => $point->customer_id,
                'points' => -$point->points,
                'transaction_type' => 'expired',
                'description' => 'Points expired on ' . $point->expires_at->format('Y-m-d'),
                'source_type' => $point->source_type,
                'source_id' => $point->source_id
            ]);
            
            // Update original point as expired
            $point->update(['transaction_type' => 'expired_processed']);
            
            $totalExpired += $point->points;
            $customersAffected[] = $point->customer_id;
        }
        
        // Notify about expiring points
        if ($totalExpired > 0) {
            $this->hub->notify(PointsExpiring::class, [
                'total_expired' => $totalExpired,
                'customers_affected' => array_unique($customersAffected),
                'expiration_date' => now()->format('Y-m-d')
            ]);
        }
        
        $this->info("Expired {$totalExpired} points for " . count(array_unique($customersAffected)) . " customers.");
        
        return 0;
    }
}