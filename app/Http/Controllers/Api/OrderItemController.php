<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class OrderItemController extends Controller
{
    /**
     * Update the status of an order item.
     *
     * @param Request $request
     * @param OrderItem $orderItem
     * @return JsonResponse
     */
    public function updateStatus(Request $request, OrderItem $orderItem): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'cooking', 'ready'])],
        ]);

        $orderItem->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Order item status updated successfully',
            'data' => [
                'id' => $orderItem->id,
                'status' => $orderItem->status,
            ],
        ]);
    }
}
