<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Exceptions\OrderAlreadyProcessedException;
use App\Exceptions\OrderNotDeletableException;
use App\Exceptions\ProductNotAvailableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $repo
    ) {}

    /**
     * List orders (admin sees all, staff sees own).
     */
    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->repo->list($user, $filters);
    }

    /**
     * Find a single order.
     */
    public function find(int $id): Order
    {
        return $this->repo->findOrFail($id);
    }

    /**
     * Create a new order.
     *
     * Price is ALWAYS calculated server-side from DB — never trust frontend prices.
     *
     * @throws ProductNotAvailableException
     */
    public function create(User $user, array $data): Order
    {
        // 1. Validate products exist and are available
        foreach ($data['items'] as $item) {
            if (($item['quantity'] ?? 0) <= 0) {
                throw new \InvalidArgumentException('Số lượng món ăn phải lớn hơn 0');
            }
        }

        $productIds = collect($data['items'])->pluck('product_id');
        $products   = Product::whereIn('id', $productIds)
                             ->where('is_available', true)
                             ->get();

        if ($products->count() !== $productIds->unique()->count()) {
            // Find which products are missing or unavailable
            $validIds   = $products->pluck('id');
            $invalidIds = $productIds->diff($validIds);

            // Get names of invalid products for error message
            $invalidProducts = Product::withTrashed()
                                      ->whereIn('id', $invalidIds)
                                      ->pluck('name');

            $names = $invalidProducts->isNotEmpty()
                ? $invalidProducts->implode(', ')
                : 'Một số món';

            throw new ProductNotAvailableException("{$names} không còn phục vụ");
        }

        // 2. Calculate total using DB prices (NEVER frontend prices)
        $items = collect($data['items'])->map(fn ($item) => [
            'product_id' => $item['product_id'],
            'quantity'   => $item['quantity'],
            'price'      => (int) $products->find($item['product_id'])->price,
        ]);

        $total = $items->sum(fn ($i) => $i['price'] * $i['quantity']);

        // 3. Persist via Repository
        return $this->repo->createWithItems(
            $user->id,
            $total,
            $items->toArray(),
            $data['note'] ?? null,
            $data['payment_method'] ?? 'cash',
            $data['table_number'] ?? null
        );
    }

    /**
     * Pay an order (pending → done).
     *
     * @throws OrderAlreadyProcessedException
     */
    public function pay(Order $order, string $paymentMethod = 'cash'): Order
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($order, $paymentMethod) {
            // Re-fetch from DB to get the most recent status (for 2-tab scenarios)
            $order = $order->fresh();

            if ($order->status !== OrderStatusEnum::PENDING) {
                $msg = $order->status === OrderStatusEnum::DONE
                    ? 'Đơn hàng đã được thanh toán bởi một phiên làm việc khác'
                    : 'Đơn hàng đã bị hủy, không thể thanh toán';

                throw new OrderAlreadyProcessedException($msg);
            }

            return $this->repo->updateStatus($order, OrderStatusEnum::DONE, now(), [
                'payment_method' => $paymentMethod,
            ]);
        });
    }

    /**
     * Add items to an existing order.
     */
    public function addItems(Order $order, array $data): Order
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($order, $data) {
            $order = $order->fresh();

            if ($order->status !== OrderStatusEnum::PENDING) {
                throw new OrderAlreadyProcessedException('Chỉ có thể thêm món vào đơn hàng đang chờ xử lý');
            }

            $productIds = collect($data['items'])->pluck('product_id');
            $products   = Product::whereIn('id', $productIds)
                                 ->where('is_available', true)
                                 ->get();

            if ($products->count() !== $productIds->unique()->count()) {
                throw new ProductNotAvailableException('Một số món không còn phục vụ');
            }

            $items = collect($data['items'])->map(fn ($item) => [
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => (int) $products->find($item['product_id'])->price,
            ]);

            return $this->repo->addItemsToOrder($order, $items->toArray());
        });
    }

    /**
     * Cancel an order (pending → cancelled).
     *
     * @throws OrderAlreadyProcessedException
     */
    public function cancel(Order $order): Order
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order = $order->fresh();

            if ($order->status !== OrderStatusEnum::PENDING) {
                $msg = $order->status === OrderStatusEnum::DONE
                    ? 'Đơn hàng đã được thanh toán, không thể hủy'
                    : 'Đơn hàng đã bị hủy trước đó';

                throw new OrderAlreadyProcessedException($msg);
            }

            return $this->repo->updateStatus($order, OrderStatusEnum::CANCELLED);
        });
    }

    /**
     * Delete an order (only pending orders can be deleted).
     *
     * @throws OrderNotDeletableException
     */
    public function delete(Order $order): bool
    {
        if ($order->status !== OrderStatusEnum::PENDING) {
            throw new OrderNotDeletableException('Chỉ có thể xóa đơn hàng đang chờ xử lý');
        }

        return $this->repo->delete($order);
    }
}
