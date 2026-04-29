<?php

namespace App\Repositories;

use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * List orders with pagination, filtered by user role.
     */
    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product']);

        // Staff can only see their own orders
        if ($user->role === RoleEnum::STAFF) {
            $query->where('user_id', $user->id);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query->orderByDesc('created_at')
                      ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Find an order by ID with items.
     */
    public function findOrFail(int $id): Order
    {
        return Order::with(['user', 'items.product'])->findOrFail($id);
    }

    /**
     * Create an order with items in a transaction.
     */
    public function createWithItems(int $userId, int $total, array $items, ?string $note = null): Order
    {
        return DB::transaction(function () use ($userId, $total, $items, $note) {
            $order = Order::create([
                'user_id' => $userId,
                'total'   => $total,
                'status'  => OrderStatusEnum::PENDING,
                'note'    => $note,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            return $order->load(['user', 'items.product']);
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, OrderStatusEnum $status, ?\DateTimeInterface $paidAt = null): Order
    {
        $order->update([
            'status'  => $status,
            'paid_at' => $paidAt,
        ]);

        return $order->fresh(['user', 'items.product']);
    }

    /**
     * Delete an order.
     */
    public function delete(Order $order): bool
    {
        return (bool) $order->delete();
    }
}
