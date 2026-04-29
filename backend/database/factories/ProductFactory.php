<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefixes = ['Bún đậu', 'Bún chả', 'Bún nem', 'Nem', 'Chả', 'Nước', 'Combo'];
        $suffixes = ['thịt bắp', 'chả cốm', 'nem Hà Nội', 'dồi sụn', 'đặc biệt', 'thêm', 'truyền thống', 'rán giòn', 'Hà Nội', 'Sài Gòn'];
        
        $name = fake()->randomElement($prefixes) . ' ' . fake()->randomElement($suffixes) . ' ' . fake()->unique()->numberBetween(1, 100);

        return [
            'name'         => $name,
            'price'        => fake()->numberBetween(10, 150) * 1000,
            'description'  => fake()->realText(50),
            'category'     => 'other',
            'is_available' => true,
        ];
    }

    /**
     * Mark product as unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
