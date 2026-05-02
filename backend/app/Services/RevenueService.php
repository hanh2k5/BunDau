<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Carbon\Carbon;

class RevenueService
{
    /**
     * Get revenue summary for today.
     */
    public function today(): array
    {
        return $this->byDate(now('Asia/Ho_Chi_Minh'));
    }

    /**
     * Get revenue summary for a specific date.
     */
    public function byDate(Carbon|string $date): array
    {
        // Ensure we have a Carbon object in Vietnam time
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date, 'Asia/Ho_Chi_Minh');
        } else {
            $date->setTimezone('Asia/Ho_Chi_Minh');
        }
        
        // Calculate the UTC range for this local day (VN 00:00 to 23:59)
        $start = $date->copy()->startOfDay()->setTimezone('UTC');
        $end = $date->copy()->endOfDay()->setTimezone('UTC');

        $stats = Order::where('status', OrderStatusEnum::DONE)
                       ->whereBetween('paid_at', [$start, $end])
                       ->selectRaw('
                            COUNT(*) as count, 
                            SUM(total) as revenue,
                            SUM(CASE WHEN payment_method = \'cash\' THEN total ELSE 0 END) as cash_revenue,
                            SUM(CASE WHEN payment_method = \'transfer\' THEN total ELSE 0 END) as transfer_revenue
                       ')
                       ->first();

        $count = (int) ($stats->count ?? 0);
        $revenue = (int) ($stats->revenue ?? 0);

        return [
            'date'              => $date->toDateString(),
            'total_orders'      => $count,
            'total_revenue'     => $revenue,
            'cash_revenue'      => (int) ($stats->cash_revenue ?? 0),
            'transfer_revenue'  => (int) ($stats->transfer_revenue ?? 0),
            'average_per_order' => $count > 0 ? (int) round($revenue / $count) : 0,
        ];
    }

    /**
     * Get revenue summary for a date range.
     */
    public function byRange(string $from, string $to): array
    {
        $fromDate = Carbon::parse($from, 'Asia/Ho_Chi_Minh')->startOfDay()->setTimezone('UTC');
        $toDate   = Carbon::parse($to, 'Asia/Ho_Chi_Minh')->endOfDay()->setTimezone('UTC');

        // 1. Get totals
        $totals = Order::where('status', OrderStatusEnum::DONE)
            ->whereBetween('paid_at', [$fromDate, $toDate])
            ->selectRaw('
                COUNT(*) as count, 
                SUM(total) as revenue,
                SUM(CASE WHEN payment_method = \'cash\' THEN total ELSE 0 END) as cash_revenue,
                SUM(CASE WHEN payment_method = \'transfer\' THEN total ELSE 0 END) as transfer_revenue
            ')
            ->first();

        $count = (int) ($totals->count ?? 0);
        $revenue = (int) ($totals->revenue ?? 0);

        // 2. Get daily breakdown via SQL
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $dateSql = $driver === 'pgsql' 
            ? "CAST(paid_at + INTERVAL '7 hours' AS DATE)" 
            : "DATE(DATE_ADD(paid_at, INTERVAL 7 HOUR))";

        $dailyRevenue = Order::where('status', OrderStatusEnum::DONE)
            ->whereBetween('paid_at', [$fromDate, $toDate])
            ->selectRaw("$dateSql as revenue_date, COUNT(*) as total_orders, SUM(total) as total_revenue")
            ->groupBy('revenue_date')
            ->orderBy('revenue_date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->revenue_date,
                'total_orders' => (int) $item->total_orders,
                'total_revenue' => (int) $item->total_revenue,
            ]);

        return [
            'from'              => $from,
            'to'                => $to,
            'total_orders'      => $count,
            'total_revenue'     => $revenue,
            'cash_revenue'      => (int) ($totals->cash_revenue ?? 0),
            'transfer_revenue'  => (int) ($totals->transfer_revenue ?? 0),
            'average_per_order' => $count > 0 ? (int) round($revenue / $count) : 0,
            'daily'             => $dailyRevenue,
        ];
    }

    /**
     * Get overall summary statistics.
     */
    public function summary(): array
    {
        // 1. All-time summary
        $allTime = Order::where('status', OrderStatusEnum::DONE)
            ->selectRaw('COUNT(*) as count, SUM(total) as revenue')
            ->first();

        $atCount = (int) ($allTime->count ?? 0);
        $atRevenue = (int) ($allTime->revenue ?? 0);

        // 2. Today summary
        $today = $this->today();

        return [
            'today' => [
                'total_orders'      => $today['total_orders'] ?? 0,
                'total_revenue'     => $today['total_revenue'] ?? 0,
                'cash_revenue'      => $today['cash_revenue'] ?? 0,
                'transfer_revenue'  => $today['transfer_revenue'] ?? 0,
            ],
            'all_time' => [
                'total_orders'      => $atCount,
                'total_revenue'     => $atRevenue,
                'average_per_order' => $atCount > 0 ? (int) round($atRevenue / $atCount) : 0,
            ],
            'pending_orders' => Order::where('status', OrderStatusEnum::PENDING)->count(),
        ];
    }
}
