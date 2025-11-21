<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        // Active orders count
        $activeOrdersCount = Order::whereNotIn('status', ['completed', 'cancelled'])->count();

        // Available tables count
        $availableTablesCount = Table::where('status', 'available')->count();

        // Today's sales (from payments)
        $todaySales = Payment::whereDate('created_at', today())
            ->sum('amount');

        // Today's customer count (unique orders today)
        $todayCustomers = Order::whereDate('created_at', today())->count();

        // Recent orders (last 10)
        $recentOrders = Order::with(['table', 'user.role'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'table_number' => $order->table->table_number,
                    'user_name' => $order->user->name,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'activeOrdersCount' => $activeOrdersCount,
            'availableTablesCount' => $availableTablesCount,
            'todaySales' => number_format($todaySales, 2, '.', ''),
            'todayCustomers' => $todayCustomers,
            'recentOrders' => $recentOrders,
        ]);
    }
}
