<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'table' => [
                'id' => $this->table->id,
                'table_number' => $this->table->table_number,
                'status' => $this->table->status,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role->name,
            ],
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payment' => $this->when($this->payment, function () {
                return [
                    'id' => $this->payment->id,
                    'payment_method' => $this->payment->payment_method,
                    'amount' => $this->payment->amount,
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
