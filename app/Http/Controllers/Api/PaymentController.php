<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Process payment for an order.
     * Updates order status to 'completed' and table status to 'available'.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get the order with relationships
            $order = Order::with(['table', 'payment'])->findOrFail($request->order_id);

            // Check if order is already paid
            if ($order->payment) {
                DB::rollBack();
                return response()->json([
                    'message' => 'This order has already been paid.',
                ], 422);
            }

            // Check if order is in a valid state for payment
            if (in_array($order->status, ['cancelled'])) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Cannot process payment for a cancelled order.',
                ], 422);
            }

            // Verify payment amount matches order total
            if (bccomp($request->amount, $order->total_amount, 2) !== 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Payment amount does not match order total.',
                    'expected' => $order->total_amount,
                    'received' => $request->amount,
                ], 422);
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
            ]);

            // Update order status to completed
            $order->update(['status' => 'completed']);

            // Update table status to available (only for dine-in orders with table)
            if ($order->table) {
                $order->table->update(['status' => 'available']);
            }

            // Load relationships for response
            $payment->load('order.table');

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully.',
                'data' => new PaymentResource($payment),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to process payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
