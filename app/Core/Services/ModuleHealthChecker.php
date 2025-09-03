<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ModuleHealthChecker
{
    public function checkAllModules()
    {
        $status = [];
        
        foreach (ModuleRegistry::getAvailableModules() as $module) {
            $serviceClass = ModuleRegistry::getService($module);
            
            try {
                $service = app($serviceClass);
                
                $status[$module] = [
                    'name' => $module,
                    'available' => true,
                    'healthy' => $service->isHealthy(),
                    'tables_exist' => $this->checkModuleTables($module),
                    'service_class' => $serviceClass,
                    'last_checked' => now()->toISOString()
                ];
            } catch (\Exception $e) {
                $status[$module] = [
                    'name' => $module,
                    'available' => false,
                    'healthy' => false,
                    'error' => $e->getMessage(),
                    'last_checked' => now()->toISOString()
                ];
            }
        }
        
        return $status;
    }
    
    private function checkModuleTables($module)
    {
        $expectedTables = [
            'pos' => ['pos_sales', 'pos_items'],
            'ecommerce' => ['products', 'orders', 'order_items'],
            'loyalty' => ['loyalty_points']
        ];
        
        if (!isset($expectedTables[$module])) {
            return true;
        }
        
        foreach ($expectedTables[$module] as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function getModuleMetrics()
    {
        $metrics = [];
        
        foreach (ModuleRegistry::getAvailableModules() as $module) {
            $metrics[$module] = $this->getModuleSpecificMetrics($module);
        }
        
        return $metrics;
    }
    
    private function getModuleSpecificMetrics($module)
    {
        switch ($module) {
            case 'pos':
                return [
                    'total_sales' => \App\Modules\POS\Models\PosSale::count(),
                    'today_sales' => \App\Modules\POS\Models\PosSale::whereDate('created_at', today())->count(),
                    'total_revenue' => \App\Modules\POS\Models\PosSale::sum('total_amount')
                ];
                
            case 'ecommerce':
                return [
                    'total_products' => \App\Modules\Ecommerce\Models\Product::count(),
                    'total_orders' => \App\Modules\Ecommerce\Models\Order::count(),
                    'low_stock_products' => \App\Modules\Ecommerce\Models\Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count()
                ];
                
            case 'loyalty':
                return [
                    'total_customers_with_points' => \App\Modules\Loyalty\Models\LoyaltyPoint::distinct('customer_id')->count(),
                    'total_points_issued' => \App\Modules\Loyalty\Models\LoyaltyPoint::where('transaction_type', 'earned')->sum('points'),
                    'total_points_redeemed' => \App\Modules\Loyalty\Models\LoyaltyPoint::where('transaction_type', 'redeemed')->sum('points')
                ];
                
            default:
                return [];
        }
    }
}