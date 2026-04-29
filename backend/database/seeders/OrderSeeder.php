<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $staff = User::where('role', 'staff')->get();
        
        if ($products->isEmpty() || $staff->isEmpty()) {
            return;
        }

        // Generate data for the last 60 days
        for ($i = 60; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Random number of orders per day (5 to 20)
            $numOrders = rand(5, 20);
            
            for ($j = 0; $j < $numOrders; $j++) {
                $user = $staff->random();
                
                // Create order
                $order = Order::create([
                    'user_id'    => $user->id,
                    'status'     => OrderStatusEnum::DONE,
                    'total'      => 0, // Will update after items
                    'note'       => rand(0, 5) > 4 ? 'Khách ăn tại chỗ' : null,
                    'paid_at'    => $date->copy()->addHours(rand(8, 21))->addMinutes(rand(0, 59)),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                // Add 1-4 random products
                $orderTotal = 0;
                $randomProducts = $products->random(rand(1, 4));
                
                foreach ($randomProducts as $product) {
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;
                    
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $product->price,
                    ]);
                    
                    $orderTotal += $subtotal;
                }

                $order->update(['total' => $orderTotal]);
            }
        }
    }
}
