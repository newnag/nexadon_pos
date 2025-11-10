<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get active order if exists
        $activeOrder = $this->orders->first();

        return [
            'id' => $this->id,
            'table_number' => $this->table_number,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            
            // Include active order details if exists
            'active_order' => $activeOrder ? [
                'id' => $activeOrder->id,
                'status' => $activeOrder->status,
                'total_amount' => $activeOrder->total_amount,
                'created_at' => $activeOrder->created_at->toDateTimeString(),
                'items_count' => $activeOrder->orderItems->count(),
            ] : null,
        ];
    }
}
