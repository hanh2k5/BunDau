<?php

namespace App\Repositories;

use App\Enums\OrderStatusEnum;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with optional filtering.
     */
    public function all(array $filters = []): Collection
    {
        $query = Product::query();

        // Filter by availability
        if (isset($filters['is_available'])) {
            $query->where('is_available', filter_var($filters['is_available'], FILTER_VALIDATE_BOOLEAN));
        }

        // Search by name
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Find a product by ID.
     */
    public function findOrFail(int $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Toggle product availability.
     */
    public function toggleAvailability(Product $product): Product
    {
        $product->update(['is_available' => !$product->is_available]);
        return $product->fresh();
    }

    /**
     * Soft delete a product.
     */
    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    /**
     * Check if product has pending orders.
     */
    public function hasPendingOrders(Product $product): bool
    {
        return $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->where('status', OrderStatusEnum::PENDING->value);
            })
            ->exists();
    }
}
