<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        try {
            // Get active orders count
            $activeOrdersCount = Order::whereIn('status', ['pending', 'preparing', 'ready'])
                ->count();

            // Get available tables count
            $availableTablesCount = Table::where('status', 'available')->count();

            // Get today's sales
            $todaySales = Order::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount');

            // Get today's customer count (number of completed orders)
            $todayCustomers = Order::whereDate('created_at', today())
                ->where('status', 'completed')
                ->count();

            return response()->json([
                'data' => [
                    'active_orders' => $activeOrdersCount,
                    'available_tables' => $availableTablesCount,
                    'today_sales' => number_format($todaySales, 2, '.', ''),
                    'today_customers' => $todayCustomers,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch dashboard statistics.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
