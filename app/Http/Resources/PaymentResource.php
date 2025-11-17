<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order' => [
                'id' => $this->order->id,
                'order_type' => $this->order->order_type,
                'table_number' => $this->order->table?->table_number,
                'customer_name' => $this->order->customer_name,
                'customer_phone' => $this->order->customer_phone,
                'status' => $this->order->status,
                'total_amount' => $this->order->total_amount,
            ],
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
