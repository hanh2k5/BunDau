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

        // Filter by payment method
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Filter by date (handling timezone)
        if (!empty($filters['date'])) {
            $date = \Carbon\Carbon::parse($filters['date'], 'Asia/Ho_Chi_Minh');
            $start = $date->copy()->startOfDay()->setTimezone('UTC');
            $end = $date->copy()->endOfDay()->setTimezone('UTC');
            $query->whereBetween('created_at', [$start, $end]);
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
    public function createWithItems(int $userId, int $total, array $items, ?string $note = null, string $paymentMethod = 'cash', ?string $tableNumber = null): Order
    {
        return DB::transaction(function () use ($userId, $total, $items, $note, $paymentMethod, $tableNumber) {
            // Calculate daily number
            $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');
            $start = $now->copy()->startOfDay()->setTimezone('UTC');
            $end = $now->copy()->endOfDay()->setTimezone('UTC');
            
            $lastDailyNumber = Order::whereBetween('created_at', [$start, $end])
                ->max('daily_number') ?? 0;

            $order = Order::create([
                'user_id'        => $userId,
                'daily_number'   => $lastDailyNumber + 1,
                'table_number'   => $tableNumber,
                'total'          => $total,
                'status'         => OrderStatusEnum::PENDING,
                'payment_method' => $paymentMethod,
                'note'           => $note,
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
     * Add items to an existing order.
     */
    public function addItemsToOrder(Order $order, array $items): Order
    {
        return DB::transaction(function () use ($order, $items) {
            $newItemsTotal = 0;
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
                $newItemsTotal += $item['price'] * $item['quantity'];
            }

            $order->increment('total', $newItemsTotal);

            return $order->fresh(['user', 'items.product']);
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, OrderStatusEnum $status, ?\DateTimeInterface $paidAt = null, array $additionalData = []): Order
    {
        $order->update(array_merge([
            'status'  => $status,
            'paid_at' => $paidAt,
        ], $additionalData));

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
