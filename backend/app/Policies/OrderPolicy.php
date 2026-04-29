<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Admin can view any, staff can only view own orders.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->role === RoleEnum::ADMIN || $order->user_id === $user->id;
    }

    /**
     * Admin can pay any, staff can only pay own orders.
     */
    public function pay(User $user, Order $order): bool
    {
        return $user->role === RoleEnum::ADMIN || $order->user_id === $user->id;
    }

    /**
     * Admin can cancel any, staff can only cancel own orders.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->role === RoleEnum::ADMIN || $order->user_id === $user->id;
    }

    /**
     * Only admin can delete orders.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->role === RoleEnum::ADMIN;
    }
}
