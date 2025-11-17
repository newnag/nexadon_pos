<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderHistoryController extends Controller
{
    public function index()
    {
        return Inertia::render('OrderHistory');
    }

    public function getOrders(Request $request)
    {
        // Check authentication for API requests
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $query = Order::with([
            'table',
            'user',
            'orderItems.menuItem.category',
            'orderItems.modifiers',
            'payments'
        ]);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('from') && $request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to') && $request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search by table number, customer info, or order ID
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('table', function ($q) use ($search) {
                        $q->where('table_number', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    public function show($id)
    {
        // Check authentication for API requests
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $order = Order::with([
            'table',
            'user',
            'orderItems.menuItem.category',
            'orderItems.modifiers',
            'payments'
        ])->findOrFail($id);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_type' => $order->order_type,
                'table' => $order->table ? [
                    'id' => $order->table->id,
                    'table_number' => $order->table->table_number,
                ] : null,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'waiter' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ],
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_item' => [
                            'id' => $item->menuItem->id,
                            'name' => $item->menuItem->name,
                            'price' => $item->menuItem->price,
                            'category' => $item->menuItem->category->name,
                        ],
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                        'status' => $item->status,
                        'modifiers' => $item->modifiers->map(function ($modifier) {
                            return [
                                'id' => $modifier->id,
                                'name' => $modifier->name,
                                'price_change' => $modifier->price_change,
                            ];
                        }),
                    ];
                }),
                'payments' => $order->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_method' => $payment->payment_method,
                        'amount' => $payment->amount,
                        'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ]
        ]);
    }
}
