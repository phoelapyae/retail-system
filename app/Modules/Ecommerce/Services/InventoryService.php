<?php

namespace App\Modules\Ecommerce\Services;

use App\Modules\Ecommerce\Models\Product;
use App\Core\Events\InventoryUpdated;
use App\Core\Services\ModuleCommunicationHub;

class InventoryService
{
    private $hub;
    
    public function __construct(ModuleCommunicationHub $hub)
    {
        $this->hub = $hub;
    }
    
    public function updateStock($sku, $quantity, $operation = 'decrease')
    {
        $product = Product::where('sku', $sku)->first();
        
        if (!$product) {
            return false;
        }
        
        $oldQuantity = $product->stock_quantity;
        
        if ($operation === 'decrease') {
            $newQuantity = max(0, $oldQuantity - $quantity);
        } else {
            $newQuantity = $oldQuantity + $quantity;
        }
        
        $product->update(['stock_quantity' => $newQuantity]);
        
        // Notify other modules
        $this->hub->notify(InventoryUpdated::class, [
            'sku' => $sku,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'change' => $newQuantity - $oldQuantity,
            'source' => 'ecommerce'
        ]);
        
        return $product;
    }
    
    public function bulkUpdateStock($updates)
    {
        $results = [];
        
        foreach ($updates as $update) {
            $results[] = $this->updateStock(
                $update['sku'],
                $update['quantity'],
                $update['operation'] ?? 'decrease'
            );
        }
        
        return $results;
    }
    
    public function getLowStockProducts($threshold = null)
    {
        $query = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        
        if ($threshold) {
            $query->where('stock_quantity', '<=', $threshold);
        }
        
        return $query->get();
    }
    
    public function getStockReport()
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'out_of_stock_products' => Product::where('stock_quantity', 0)->count(),
            'total_stock_value' => Product::selectRaw('SUM(stock_quantity * cost) as total_value')->first()->total_value ?? 0
        ];
    }
}