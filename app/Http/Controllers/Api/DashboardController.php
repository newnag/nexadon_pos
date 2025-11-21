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

            // Get recent orders (last 5)
            $recentOrders = Order::with(['table', 'user'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'table_number' => $order->table ? $order->table->table_number : 'N/A',
                        'user_name' => $order->user ? $order->user->name : 'Unknown',
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at->format('H:i'),
                    ];
                });

            return response()->json([
                'activeOrdersCount' => $activeOrdersCount,
                'availableTablesCount' => $availableTablesCount,
                'todaySales' => number_format($todaySales, 2, '.', ''),
                'todayCustomers' => $todayCustomers,
                'recentOrders' => $recentOrders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch dashboard statistics.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
