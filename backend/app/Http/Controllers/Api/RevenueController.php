<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService
    ) {}

    /**
     * Get today's revenue.
     */
    public function today(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->revenueService->today(),
        ]);
    }

    /**
     * Get overall summary.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->revenueService->summary(),
        ]);
    }

    /**
     * Get revenue by specific date.
     */
    public function byDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->revenueService->byDate($request->date),
        ]);
    }

    /**
     * Get revenue by date range.
     */
    public function byRange(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to'   => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $this->revenueService->byRange($request->from, $request->to),
        ]);
    }
}
