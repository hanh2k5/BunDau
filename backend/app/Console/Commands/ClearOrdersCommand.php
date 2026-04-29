<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

class ClearOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa toàn bộ đơn hàng và chi tiết đơn hàng nhưng GIỮ LẠI sản phẩm và người dùng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Bạn có chắc chắn muốn XÓA TOÀN BỘ đơn hàng không? (Sản phẩm sẽ được giữ nguyên)')) {
            $this->info('Đang bắt đầu dọn dẹp...');

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            $orderCount = Order::count();
            OrderItem::truncate();
            Order::truncate();
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->success("Đã xóa sạch {$orderCount} đơn hàng thành công! Thực đơn của bạn vẫn an toàn. 🍱");
        }
    }
}
