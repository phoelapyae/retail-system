<?php

namespace App\Modules\Ecommerce\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ecommerce\Services\EcommerceService;
use App\Modules\Ecommerce\Requests\CreateOrderRequest;
use App\Modules\Ecommerce\Resources\OrderResource;
use App\Modules\Ecommerce\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $ecommerceService;
    
    public function __construct(EcommerceService $ecommerceService)
    {
        $this->ecommerceService = $ecommerceService;
    }
    
    public function index(Request $request)
    {
        $query = Order::with('customer', 'items');
        
        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Customer filter
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate($request->per_page ?? 15);
        
        return OrderResource::collection($orders);
    }
    
    public function create(CreateOrderRequest $request)
    {
        try {
            $order = $this->ecommerceService->createOrder($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => new OrderResource($order),
                'message' => 'Order created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $order = Order::with('items', 'customer')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => new OrderResource($order)
        ]);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);
        
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
            'message' => 'Order status updated successfully'
        ]);
    }
}