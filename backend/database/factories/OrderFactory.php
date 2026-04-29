<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'staff')->inRandomOrder()->first()?->id ?? User::factory()->staff(),
            'total'   => 0,
            'status'  => 'pending',
            'note'    => null,
            'paid_at' => null,
        ];
    }

    /**
     * Mark order as done (paid).
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'done',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark order as pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'pending',
            'paid_at' => null,
        ]);
    }

    /**
     * Mark order as cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'cancelled',
            'paid_at' => null,
        ]);
    }

    /**
     * Set order created today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now(),
        ]);
    }

    /**
     * Set order created N days ago.
     */
    public function daysAgo(int $days): static
    {
        $date = now()->subDays($days);

        return $this->state(fn (array $attributes) => [
            'created_at' => $date,
            'paid_at'    => $attributes['status'] === 'done' ? $date : null,
        ]);
    }

    /**
     * After creating, add random items and calculate total.
     */
    public function withItems(?Collection $products = null): static
    {
        return $this->afterCreating(function (Order $order) use ($products) {
            $products = $products ?? Product::all();

            if ($products->isEmpty()) {
                return;
            }

            $items = $products->random(min(rand(1, 4), $products->count()));
            $total = 0;

            foreach ($items as $product) {
                $qty = rand(1, 3);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $product->price,
                ]);
                $total += $product->price * $qty;
            }

            $order->update(['total' => $total]);
        });
    }
}
