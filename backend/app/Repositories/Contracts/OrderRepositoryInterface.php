<?php

namespace App\Repositories\Contracts;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * List orders with pagination, filtered by user role.
     */
    public function list(User $user, array $filters = []): LengthAwarePaginator;

    /**
     * Find an order by ID with items.
     */
    public function findOrFail(int $id): Order;

    /**
     * Create an order with items in a transaction.
     */
    public function createWithItems(int $userId, int $total, array $items, ?string $note = null, string $paymentMethod = 'cash', ?string $tableNumber = null): Order;

    /**
     * Add items to an existing order.
     */
    public function addItemsToOrder(Order $order, array $items): Order;

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, OrderStatusEnum $status, ?\DateTimeInterface $paidAt = null, array $additionalData = []): Order;

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool;
}
