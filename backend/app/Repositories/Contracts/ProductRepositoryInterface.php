<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all products with optional filtering.
     */
    public function all(array $filters = []): Collection;

    /**
     * Find a product by ID.
     */
    public function findOrFail(int $id): Product;

    /**
     * Create a new product.
     */
    public function create(array $data): Product;

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product;

    /**
     * Toggle product availability.
     */
    public function toggleAvailability(Product $product): Product;

    /**
     * Soft delete a product.
     */
    public function delete(Product $product): bool;

    /**
     * Check if product has pending orders.
     */
    public function hasPendingOrders(Product $product): bool;
}
