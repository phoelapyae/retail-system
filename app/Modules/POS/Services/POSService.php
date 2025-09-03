<?php

namespace App\Modules\POS\Services;

use App\Core\Interfaces\ModuleServiceInterface;
use App\Core\Events\{ModuleEvent, SaleCompleted, InventoryUpdated};
use App\Core\Services\ModuleCommunicationHub;
use App\Modules\POS\Models\{PosSale, PosItem};
use App\Core\Models\Customer;

class POSService implements ModuleServiceInterface
{
    private $hub;
    
    public function __construct(ModuleCommunicationHub $hub)
    {
        $this->hub = $hub;
    }
    
    public function completeSale($saleData)
    {
        // 1. Create the sale
        $sale = $this->createSale($saleData);
        
        // 2. Add items to sale
        if (isset($saleData['items'])) {
            foreach ($saleData['items'] as $itemData) {
                $this->addItemToSale($sale, $itemData);
            }
        }
        
        // 3. Calculate totals
        $this->calculateTotals($sale);
        
        // 4. Notify other modules about the completed sale
        $this->hub->notify(SaleCompleted::class, [
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'total_amount' => $sale->total_amount,
            'items' => $sale->items->toArray(),
            'source' => 'pos'
        ]);
        
        // 5. Update inventory
        $this->updateInventory($sale);
        
        return $sale->load('items', 'customer');
    }
    
    private function createSale($data)
    {
        // Find or create customer if provided
        $customerId = null;
        if (isset($data['customer'])) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['customer']['email']], 
                $data['customer']
            );
            $customerId = $customer->id;
        }
        
        return PosSale::create([
            'customer_id' => $customerId,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'status' => 'pending',
            'register_id' => $data['register_id'] ?? 1,
            'cashier_id' => $data['cashier_id'] ?? auth()->id(),
            'receipt_number' => $this->generateReceiptNumber()
        ]);
    }
    
    private function addItemToSale($sale, $itemData)
    {
        return PosItem::create([
            'pos_sale_id' => $sale->id,
            'product_sku' => $itemData['sku'],
            'product_name' => $itemData['name'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'total_price' => $itemData['quantity'] * $itemData['unit_price']
        ]);
    }
    
    private function calculateTotals($sale)
    {
        $subtotal = $sale->items()->sum('total_price');
        $taxAmount = $subtotal * 0.08; // 8% tax
        $totalAmount = $subtotal + $taxAmount;
        
        $sale->update([
            'total_amount' => $totalAmount,
            'tax_amount' => $taxAmount,
            'status' => 'completed'
        ]);
    }
    
    private function updateInventory($sale)
    {
        // Notify ecommerce module about inventory changes
        $inventoryUpdates = $sale->items->map(function ($item) {
            return [
                'sku' => $item->product_sku,
                'quantity_sold' => $item->quantity,
                'source' => 'pos_sale'
            ];
        })->toArray();
        
        $this->hub->notify(InventoryUpdated::class, [
            'updates' => $inventoryUpdates,
            'sale_id' => $sale->id
        ]);
    }
    
    private function generateReceiptNumber()
    {
        return 'POS-' . date('Ymd') . '-' . str_pad(PosSale::count() + 1, 4, '0', STR_PAD_LEFT);
    }
    
    public function handleEvent(ModuleEvent $event)
    {
        if ($event instanceof InventoryUpdated && $event->data['source'] !== 'pos_sale') {
            // Handle inventory updates from other modules
            \Log::info('POS: Received inventory update', $event->data);
        }
    }
    
    public function isHealthy(): bool
    {
        try {
            return PosSale::count() >= 0; // Simple health check
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getModuleName(): string
    {
        return 'pos';
    }
    
    public function getSalesByCustomer($customerId)
    {
        return PosSale::where('customer_id', $customerId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}