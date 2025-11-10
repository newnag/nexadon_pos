<?php

namespace App\Http\Controllers\Api;

use App\Events\NewOrderPlaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Create a new order with items.
     * Calculate total price and update table status to 'occupied'.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get the table and check if it's available
            $table = Table::findOrFail($request->table_id);
            
            if ($table->status === 'occupied') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Table is already occupied. Please select another table.',
                ], 422);
            }

            // Create the order
            $order = Order::create([
                'table_id' => $request->table_id,
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'total_amount' => 0, // Will calculate below
            ]);

            $totalAmount = 0;

            // Create order items
            foreach ($request->order_items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);

                // Check if menu item is available
                if (!$menuItem->is_available) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Menu item '{$menuItem->name}' is currently unavailable.",
                    ], 422);
                }

                // Create order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Attach modifiers if provided
                if (!empty($item['modifier_ids'])) {
                    $orderItem->modifiers()->attach($item['modifier_ids']);
                }

                // Calculate item total (price + modifiers) * quantity
                $modifiersTotal = 0;
                if (!empty($item['modifier_ids'])) {
                    $modifiersTotal = $orderItem->modifiers()->sum('price_change');
                }
                
                $itemTotal = ($menuItem->price + $modifiersTotal) * $item['quantity'];
                $totalAmount += $itemTotal;
            }

            // Update order total amount
            $order->update(['total_amount' => $totalAmount]);

            // Update table status to occupied
            $table->update(['status' => 'occupied']);

            // Load relationships for response
            $order->load(['table', 'user.role', 'orderItems.menuItem.category', 'orderItems.modifiers']);

            DB::commit();

            // Broadcast the new order to the kitchen display system
            broadcast(new NewOrderPlaced($order))->toOthers();

            return response()->json([
                'message' => 'Order created successfully.',
                'data' => new OrderResource($order),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all active orders (not completed or cancelled).
     */
    public function active(): JsonResponse
    {
        $orders = Order::with(['table', 'user.role', 'orderItems.menuItem.category', 'orderItems.modifiers'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => OrderResource::collection($orders),
        ], 200);
    }

    /**
     * Get single order details (for billing page).
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['table', 'user.role', 'orderItems.menuItem.category', 'orderItems.modifiers', 'payment']);

        return response()->json([
            'data' => new OrderResource($order),
        ], 200);
    }

    /**
     * Update an existing order (add more items).
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check if order can be modified
            if (in_array($order->status, ['completed', 'cancelled'])) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Cannot modify a completed or cancelled order.',
                ], 422);
            }

            // Update status if provided
            if ($request->has('status')) {
                $order->update(['status' => $request->status]);
            }

            // Add new order items
            $totalAmount = $order->total_amount;

            foreach ($request->order_items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);

                // Check if menu item is available
                if (!$menuItem->is_available) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Menu item '{$menuItem->name}' is currently unavailable.",
                    ], 422);
                }

                // Create order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Attach modifiers if provided
                if (!empty($item['modifier_ids'])) {
                    $orderItem->modifiers()->attach($item['modifier_ids']);
                }

                // Calculate item total (price + modifiers) * quantity
                $modifiersTotal = 0;
                if (!empty($item['modifier_ids'])) {
                    $modifiersTotal = $orderItem->modifiers()->sum('price_change');
                }
                
                $itemTotal = ($menuItem->price + $modifiersTotal) * $item['quantity'];
                $totalAmount += $itemTotal;
            }

            // Update order total amount
            $order->update(['total_amount' => $totalAmount]);

            // Load relationships for response
            $order->load(['table', 'user.role', 'orderItems.menuItem.category', 'orderItems.modifiers']);

            DB::commit();

            return response()->json([
                'message' => 'Order updated successfully.',
                'data' => new OrderResource($order),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
