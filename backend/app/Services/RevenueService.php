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
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $dateSql = $driver === 'pgsql' 
            ? "CAST(paid_at + INTERVAL '7 hours' AS DATE)" 
            : "DATE(DATE_ADD(paid_at, INTERVAL 7 HOUR))";

        $stats = Order::where('status', OrderStatusEnum::DONE)
                       ->whereRaw("$dateSql = ?", [$date->toDateString()])
                       ->selectRaw('COUNT(*) as count, SUM(total) as revenue')
                       ->first();

        return [
            'date'         => $date->toDateString(),
            'total_orders' => (int) $stats->count,
            'total_revenue' => (int) $stats->revenue,
            'average_per_order' => $stats->count > 0
                ? (int) round($stats->revenue / $stats->count)
                : 0,
        ];
    }

    /**
     * Get revenue summary for a date range.
     */
    public function byRange(string $from, string $to): array
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $dateSql = $driver === 'pgsql' 
            ? "CAST(paid_at + INTERVAL '7 hours' AS DATE)" 
            : "DATE(DATE_ADD(paid_at, INTERVAL 7 HOUR))";

        // 1. Get totals via SQL - Using the same timezone-aware logic for filtering
        $totals = Order::where('status', OrderStatusEnum::DONE)
            ->whereRaw("$dateSql BETWEEN ? AND ?", [$from, $to])
            ->selectRaw('COUNT(*) as count, SUM(total) as revenue')
            ->first();

        // 2. Get daily breakdown via SQL
        $dailyRevenue = Order::where('status', OrderStatusEnum::DONE)
            ->whereRaw("$dateSql BETWEEN ? AND ?", [$from, $to])
            ->selectRaw("$dateSql as date, COUNT(*) as total_orders, SUM(total) as total_revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total_orders' => (int) $item->total_orders,
                'total_revenue' => (int) $item->total_revenue,
            ]);

        return [
            'from'              => $from,
            'to'                => $to,
            'total_orders'      => (int) $totals->count,
            'total_revenue'     => (int) $totals->revenue,
            'average_per_order' => $totals->count > 0
                ? (int) round($totals->revenue / $totals->count)
                : 0,
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

        // 2. Today summary
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $dateSql = $driver === 'pgsql' 
            ? "CAST(paid_at + INTERVAL '7 hours' AS DATE)" 
            : "DATE(DATE_ADD(paid_at, INTERVAL 7 HOUR))";

        $today = Order::where('status', OrderStatusEnum::DONE)
            ->whereRaw("$dateSql = ?", [now('Asia/Ho_Chi_Minh')->toDateString()])
            ->selectRaw('COUNT(*) as count, SUM(total) as revenue')
            ->first();

        return [
            'today' => [
                'total_orders'  => (int) $today->count,
                'total_revenue' => (int) $today->revenue,
            ],
            'all_time' => [
                'total_orders'      => (int) $allTime->count,
                'total_revenue'     => (int) $allTime->revenue,
                'average_per_order' => $allTime->count > 0
                    ? (int) round($allTime->revenue / $allTime->count)
                    : 0,
            ],
            'pending_orders' => Order::where('status', OrderStatusEnum::PENDING)->count(),
        ];
    }
}
