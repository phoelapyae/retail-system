<?php

namespace App\Modules\Ecommerce\Services;

use App\Core\Interfaces\ModuleServiceInterface;
use App\Core\Events\{ModuleEvent, OrderCreated, InventoryUpdated, SaleCompleted};
use App\Core\Services\ModuleCommunicationHub;
use App\Modules\Ecommerce\Models\{Product, Order, OrderItem};
use App\Core\Models\Customer;

class EcommerceService implements ModuleServiceInterface
{
    private $hub;
    
    public function __construct(ModuleCommunicationHub $hub)
    {
        $this->hub = $hub;
    }
    
    public function createOrder($orderData)
    {
        // 1. Create the order
        $order = $this->buildOrder($orderData);
        
        // 2. Add items and update inventory
        if (isset($orderData['items'])) {
            foreach ($orderData['items'] as $itemData) {
                $this->addItemToOrder($order, $itemData);
            }
        }
        
        // 3. Calculate totals
        $this->calculateOrderTotals($order);
        
        // 4. Notify other modules
        $this->hub->notify(OrderCreated::class, [
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'total_amount' => $order->total_amount,
            'items' => $order->items->toArray(),
            'source' => 'ecommerce'
        ]);
        
        return $order->load('items', 'customer');
    }
    
    private function buildOrder($data)
    {
        // Find or create customer
        $customer = Customer::firstOrCreate(
            ['email' => $data['customer']['email']], 
            $data['customer']
        );
        
        return Order::create([
            'customer_id' => $customer->id,
            'order_number' => $this->generateOrderNumber(),
            'status' => 'pending',
            'payment_method' => $data['payment_method'] ?? 'online',
            'shipping_address' => $data['shipping_address'] ?? null
        ]);
    }
    
    private function addItemToOrder($order, $itemData)
    {
        $product = Product::where('sku', $itemData['sku'])->first();
        
        if (!$product || $product->stock_quantity < $itemData['quantity']) {
            throw new \Exception("Insufficient stock for product: {$itemData['sku']}");
        }
        
        // Decrease stock
        $product->decreaseStock($itemData['quantity']);
        
        // Create order item
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_sku' => $product->sku,
            'product_name' => $product->name,
            'quantity' => $itemData['quantity'],
            'unit_price' => $product->price,
            'total_price' => $product->price * $itemData['quantity']
        ]);
    }
    
    private function calculateOrderTotals($order)
    {
        $subtotal = $order->items()->sum('total_price');
        $taxAmount = $subtotal * 0.08;
        $shippingAmount = $subtotal >= 100 ? 0 : 10; // Free shipping over $100
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;
        
        $order->update([
            'total_amount' => $totalAmount,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount
        ]);
    }
    
    private function generateOrderNumber()
    {
        return 'EC-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT);
    }
    
    public function handleEvent(ModuleEvent $event)
    {
        if ($event instanceof SaleCompleted) {
            // Sync inventory when POS makes a sale
            $this->syncInventoryFromPOS($event->data);
        }
    }
    
    private function syncInventoryFromPOS($saleData)
    {
        if (isset($saleData['items'])) {
            foreach ($saleData['items'] as $item) {
                $product = Product::where('sku', $item['product_sku'])->first();
                if ($product) {
                    $product->decreaseStock($item['quantity']);
                }
            }
        }
    }
    
    public function isHealthy(): bool
    {
        try {
            return Product::count() >= 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getModuleName(): string
    {
        return 'ecommerce';
    }
    
    public function getProductsBySku($skus)
    {
        return Product::whereIn('sku', $skus)->get();
    }
}