<?php

namespace App\Modules\Ecommerce\Controllers;

use App\Core\Services\ModuleCommunicationHub;
use App\Http\Controllers\Controller;
use App\Modules\Ecommerce\Models\Product;
use App\Modules\Ecommerce\Requests\{CreateProductRequest, UpdateProductRequest};
use App\Modules\Ecommerce\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $hub;

    public function __construct(
        ModuleCommunicationHub $hub
    )
    {
        $this->hub = $hub;
    }
    public function index(Request $request)
    {

        $students = $this->hub->request('loyalty', 'studentList', []);

        return $students;

        $query = Product::query();
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Low stock filter
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        }
        
        $products = $query->paginate($request->per_page ?? 15);
        
        return ProductResource::collection($products);
    }
    
    public function store(CreateProductRequest $request)
    {
        $product = Product::create($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product created successfully'
        ], 201);
    }
    
    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
        ]);
    }
    
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product updated successfully'
        ]);
    }
    
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
    
    public function lowStock()
    {
        $products = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get();
        
        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }
}