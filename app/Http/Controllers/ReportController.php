<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports');
    }

    public function getSalesReport(Request $request)
    {
        // Check authentication for API requests
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = $request->from . ' 00:00:00';
        $to = $request->to . ' 23:59:59';

        // Summary Statistics
        $totalRevenue = Payment::whereBetween('created_at', [$from, $to])->sum('amount');
        $totalOrders = Order::whereBetween('created_at', [$from, $to])->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalItemsSold = OrderItem::whereHas('order', function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        })->sum('quantity');

        // Top Selling Items
        $topSellingItems = DB::table('order_items')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'menu_items.id',
                'menu_items.name',
                'categories.name as category',
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.quantity * menu_items.price) as revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name', 'categories.name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'quantity' => (int) $item->quantity,
                    'revenue' => number_format($item->revenue, 2),
                ];
            });

        // Revenue by Category
        $revenueByCategory = DB::table('order_items')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'categories.name',
                DB::raw('SUM(order_items.quantity * menu_items.price) as revenue')
            )
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($category) use ($totalRevenue) {
                $revenue = (float) $category->revenue;
                $percentage = $totalRevenue > 0 ? ($revenue / $totalRevenue) * 100 : 0;
                return [
                    'name' => $category->name,
                    'revenue' => number_format($revenue, 2),
                    'percentage' => round($percentage, 1),
                ];
            });

        return response()->json([
            'summary' => [
                'totalRevenue' => number_format($totalRevenue, 2),
                'totalOrders' => $totalOrders,
                'avgOrderValue' => number_format($avgOrderValue, 2),
                'totalItemsSold' => $totalItemsSold,
            ],
            'topSellingItems' => $topSellingItems,
            'revenueByCategory' => $revenueByCategory,
        ]);
    }
}
