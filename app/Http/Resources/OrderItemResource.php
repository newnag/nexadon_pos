<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate item subtotal (price * quantity + modifiers)
        $itemPrice = $this->menuItem->price;
        $modifiersTotal = $this->modifiers->sum('price_change');
        $subtotal = ($itemPrice + $modifiersTotal) * $this->quantity;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'status' => $this->status,
            'menu_item' => [
                'id' => $this->menuItem->id,
                'name' => $this->menuItem->name,
                'price' => $this->menuItem->price,
                'category' => $this->menuItem->category->name,
            ],
            'modifiers' => $this->modifiers->map(function ($modifier) {
                return [
                    'id' => $modifier->id,
                    'name' => $modifier->name,
                    'price_change' => $modifier->price_change,
                ];
            }),
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
