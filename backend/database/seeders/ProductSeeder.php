<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Product::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $products = [
            // --- Bún Đậu ---
            ['name' => 'Bún đậu thịt bắp', 'price' => 40000, 'description' => 'Bún đậu kèm thịt bắp thơm ngon', 'category' => 'bun-dau'],
            ['name' => 'Bún đậu chả cốm', 'price' => 40000, 'description' => 'Bún đậu kèm chả cốm giòn rụm', 'category' => 'bun-dau'],
            ['name' => 'Bún đậu nem Hà Nội', 'price' => 40000, 'description' => 'Bún đậu kèm nem Hà Nội đặc sản', 'category' => 'bun-dau'],
            ['name' => 'Bún đậu dồi sụn', 'price' => 40000, 'description' => 'Bún đậu kèm dồi sụn thơm béo', 'category' => 'bun-dau'],
            ['name' => 'Bún đậu nem chua rán', 'price' => 40000, 'description' => 'Bún đậu kèm nem chua rán giòn rụm', 'category' => 'bun-dau'],

            // --- Bún Chả / Bún Nem ---
            ['name' => 'Bún chả Hà Nội', 'price' => 40000, 'description' => 'Bún chả Hà Nội truyền thống', 'category' => 'bun-cha'],
            ['name' => 'Bún nem Hà Nội', 'price' => 40000, 'description' => 'Bún nem Hà Nội giòn tan', 'category' => 'bun-cha'],
            ['name' => 'Bún chả + nem Hà Nội', 'price' => 55000, 'description' => 'Combo bún chả và nem Hà Nội', 'category' => 'bun-cha'],

            // --- Combo + Trà Tắc ---
            ['name' => 'Combo 1 người + 1 trà', 'price' => 65000, 'description' => 'Combo tiết kiệm cho 1 người kèm trà tắc', 'category' => 'combo'],
            ['name' => 'Combo 2 người + 2 trà', 'price' => 110000, 'description' => 'Combo tiết kiệm cho 2 người kèm 2 trà tắc', 'category' => 'combo'],
            ['name' => 'Combo 3 người + 3 trà', 'price' => 165000, 'description' => 'Combo tiết kiệm cho 3 người kèm 3 trà tắc', 'category' => 'combo'],
            ['name' => 'Combo 4 người + 4 trà', 'price' => 220000, 'description' => 'Combo tiết kiệm cho 4 người kèm 4 trà tắc', 'category' => 'combo'],

            // --- Món Thêm ---
            ['name' => 'Bún thêm (bún đậu)', 'price' => 5000, 'description' => 'Thêm 1 phần bún đậu', 'category' => 'other'],
            ['name' => 'Bún thêm (bún chả)', 'price' => 5000, 'description' => 'Thêm 1 phần bún chả', 'category' => 'other'],
            ['name' => 'Đậu thêm', 'price' => 10000, 'description' => 'Thêm 1 phần đậu hũ', 'category' => 'other'],
            ['name' => 'Chả cốm (thêm)', 'price' => 25000, 'description' => 'Thêm 1 phần chả cốm', 'category' => 'other'],
            ['name' => 'Dồi sụn (thêm)', 'price' => 25000, 'description' => 'Thêm 1 phần dồi sụn', 'category' => 'other'],
            ['name' => 'Phèo (thêm)', 'price' => 25000, 'description' => 'Thêm 1 phần phèo', 'category' => 'other'],
            ['name' => 'Thịt (thêm)', 'price' => 25000, 'description' => 'Thêm 1 phần thịt', 'category' => 'other'],
            ['name' => 'Nem chua rán (thêm)', 'price' => 20000, 'description' => 'Thêm 1 phần nem chua rán', 'category' => 'other'],
            ['name' => 'Nem Hà Nội (thêm)', 'price' => 20000, 'description' => 'Thêm 1 phần nem Hà Nội', 'category' => 'other'],
            ['name' => 'Thịt chả thêm', 'price' => 30000, 'description' => 'Thêm thịt chả cho bún chả', 'category' => 'other'],


            // --- Nước ---
            ['name' => 'Trà tắc', 'price' => 5000, 'description' => 'Trà tắc thơm mát', 'category' => 'drink'],
            ['name' => 'Coca', 'price' => 15000, 'description' => 'Coca Cola', 'category' => 'drink'],
            ['name' => 'Pepsi', 'price' => 15000, 'description' => 'Pepsi', 'category' => 'drink'],
            ['name' => 'Sting', 'price' => 15000, 'description' => 'Sting dâu', 'category' => 'drink'],
            ['name' => 'Nước suối', 'price' => 10000, 'description' => 'Nước suối đóng chai', 'category' => 'drink'],

            // --- Combo (Không nước) ---
            ['name' => 'Combo 1 người', 'price' => 60000, 'description' => 'Combo bún đậu cho 1 người', 'category' => 'combo'],
            ['name' => 'Combo 2 người', 'price' => 100000, 'description' => 'Combo bún đậu cho 2 người', 'category' => 'combo'],
            ['name' => 'Combo 3 người', 'price' => 150000, 'description' => 'Combo bún đậu cho 3 người', 'category' => 'combo'],
            ['name' => 'Combo 4 người', 'price' => 200000, 'description' => 'Combo bún đậu cho 4 người', 'category' => 'combo'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
