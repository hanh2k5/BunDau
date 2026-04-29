<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class ProductController
 * @package App\Http\Controllers\Api
 * 
 * Handles all product-related operations using ProductService for business logic.
 */
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Display a listing of the products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->productService->list($request->all());

        return ProductResource::collection($products);
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->find($id);

        return response()->json([
            'success' => true,
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo sản phẩm thành công',
            'data'    => new ProductResource($product),
        ], 201);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Toggle product availability.
     */
    public function toggle(Product $product): JsonResponse
    {
        $product = $this->productService->toggleAvailability($product);

        return response()->json([
            'success' => true,
            'message' => $product->is_available ? 'Đã bật sản phẩm' : 'Đã tắt sản phẩm',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return response()->json([
            'success' => true,
            'message' => 'Xóa sản phẩm thành công',
        ]);
    }
}
