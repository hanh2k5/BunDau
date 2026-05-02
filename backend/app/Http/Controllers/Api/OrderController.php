<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * List orders (admin: all, staff: own).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->list($request->user(), $request->all());

        return OrderResource::collection($orders);
    }

    /**
     * Show a single order.
     */
    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order = $this->orderService->find($order->id);

        return response()->json([
            'success' => true,
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công',
            'data'    => new OrderResource($order),
        ], 201);
    }

    /**
     * Pay an order (pending → done).
     */
    public function pay(Order $order, Request $request): JsonResponse
    {
        Gate::authorize('pay', $order);

        $request->validate([
            'payment_method' => 'required|in:cash,transfer',
        ]);

        $order = $this->orderService->pay($order, $request->payment_method);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công',
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * Add items to an existing order.
     */
    public function addItems(Order $order, StoreOrderRequest $request): JsonResponse
    {
        Gate::authorize('view', $order); // Reuse view permission

        $order = $this->orderService->addItems($order, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Thêm món thành công',
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * Cancel an order (pending → cancelled).
     */
    public function cancel(Order $order): JsonResponse
    {
        Gate::authorize('cancel', $order);

        $order = $this->orderService->cancel($order);

        return response()->json([
            'success' => true,
            'message' => 'Hủy đơn hàng thành công',
            'data'    => new OrderResource($order),
        ]);
    }

    /**
     * Delete an order (admin only, pending only).
     */
    public function destroy(Order $order): JsonResponse
    {
        Gate::authorize('delete', $order);

        $this->orderService->delete($order);

        return response()->json([
            'success' => true,
            'message' => 'Xóa đơn hàng thành công',
        ]);
    }
}
