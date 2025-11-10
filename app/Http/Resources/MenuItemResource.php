<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'is_available' => $this->is_available,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'modifiers' => $this->modifiers->map(function ($modifier) {
                return [
                    'id' => $modifier->id,
                    'name' => $modifier->name,
                    'price_change' => $modifier->price_change,
                ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
